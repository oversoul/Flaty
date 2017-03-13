<?php
namespace Core;

class App
{
    /**
     * @var instance of app.
     */
    protected static $instance = null;

    /**
     * @var containing instances of classes
     */
    protected static $objects = [];

    /**
     * @var Current request uri
     */
    public $uri;

    /**
     * @var Current theme
     */
    public $theme;

    /**
     * @var main config
     */
    public $config;

    /**
     * @var defined routes
     */
    protected $routes = [];

    /**
     * Loading instance of App class
     * @return this
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Firing the start of the app
     * @param config array
     * @return instance of Response
     */
    public function run($config = [])
    {
        $this->config = $config;
        $this->theme = $this->config->site->theme;
        $this->set('request', new Request);
        $this->set('view', new View);

        $this->uri = rtrim($this->request->uri(), '/');

        $this->plugins();

        $this->event->trigger('after.plugin');

        $this->event->trigger('before.routes', [ & $this->routes]);
        $name = $this->dispatch();
        $this->event->trigger('after.routes', [$this->routes]);
        if ($name instanceof Response) {
            return $name;
        }

        if (is_null($name)) {
            $name = $this->uri;
        }

        $this->event->trigger('before.parse', [$name]);
        $this->parse($name);
        $this->internalPlugin();

        $this->event->trigger('after.parse', [ & $this->page]);

        $this->event->trigger('before.render');
        $view = $this->render();
        $this->event->trigger('after.render', [ & $view]);

        return $this->response($view);
    }

    /**
     * if internal plugin was set, load it.
     */
    public function internalPlugin()
    {
        $page = $this->page->header();
        $plugin = array_get($page, 'plugin');
        if (!$plugin) {
            return;
        }

        $plugin = ucfirst(strtolower($plugin));
        $plugin = "\Plugins\\{$plugin}\\{$plugin}";
        new $plugin($this);
    }

    /**
     * Dispatching routes or uri.
     * @return string
     */
    public function dispatch()
    {
        if (!empty($this->routes)) {

            foreach ($this->routes as $route => $callback) {
                preg_match('~^' . $route . '$~', $this->uri, $matches);
                if (!empty($matches)) {
                    if (!is_callable($callback)) {
                        throw new \Exception("Route [$route] callback not found");
                    }
                    return call_user_func_array($callback, $matches);
                }
            }
        }

        $name = trim($this->uri, '/');
        $this->uri = $name == '' ? 'index' : $name;
        return $this->uri;
    }

    /**
     * parse page.
     * 
     * @param  name page name to parse
     * @return boolean
     */
    public function parse($name)
    {
        if (is_object($this->page)) {
            return true;
        }

        $this->set('page', new Page);

        $result = $this->page->loadable(CONTENT . $name . '.md');
        if ($result === true) {
            $this->page->load();
            return true;
        }

        $result = $this->page->loadable(CONTENT . $name . DS . 'index.md');
        if ($result) {
            $this->page->load();
            return true;
        }

        $this->error("File {$name} not found");
    }

    /**
     * Render a theme
     * @param  theme name
     * @param  array
     * @param  layout
     * @return string
     */
    public function render($theme = false, $data = [], $layout = false)
    {
        $view = $this->view;

        $config = json_decode(json_encode($this->config), true);
        $view->set('config', $config);

        $view->set($data);

        $view->set('page', $this->page->header());

        $this->event->trigger('after.header', [ & $view]);

        if (!$view->get('page.layout')) {
            $view->set('page.layout', 'index');
        }

        $content = $this->page->content();
        $this->event->trigger('after.content', [ & $content]);

        $view->renderBlock('content', $content);

        if (!$theme) {
            $theme = $this->theme;
        }

        if (!$layout) {
            $layout = $view->get('page.layout');
        }

        $response = $view->render($theme . DS . $layout);

        $this->event->trigger('after.render', [ & $response]);

        return $response;
    }

    /**
     * Return a Http response.
     * @param  string
     * @return Response
     */
    public function response($view)
    {
        return new Response($view);
    }

    /**
     * Render a 404 not found page.
     * @param  string
     */
    public function error($message)
    {
        $this->page->setHeader(['layout' => '404', 'message' => $message]);
    }

    /**
     * Run plugins set in config.
     */
    public function plugins()
    {
        if (empty($this->config->plugins)) {
            return;
        }

        foreach ($this->config->plugins as $plugin) {
            $name = strtolower($plugin);
            $plugin = ucfirst(strtolower($plugin));
            $plugin = "\Plugins\\{$plugin}\\{$plugin}";
            new $plugin($this);
        }
    }

    /**
     * Get class using key.
     * @param  class name
     * @return object
     */
    public function get($class)
    {
        if ($class == get_class()) {
            return $this;
        }

        if (!isset(self::$objects[$class])) {
            return false;
        }

        $obj = self::$objects[$class];
        if (is_string($obj)) {
            return new $obj;
        }

        return $obj;
    }

    /**
     * Set an object instance or name.
     * @param string
     * @param string|object
     */
    public function set($key, $value)
    {
        self::$objects[$key] = $value;
    }

    /**
     * Static magic method
     * @param  string
     * @param  array
     */
    public static function __callStatic($method, $args = [])
    {
        return call_user_func_array([self::instance(), $method], $args);
    }

    /**
     * Get accessor.
     * @param  string
     */
    public function __get($key)
    {
        return $this->get($key);
    }
}
