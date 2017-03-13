<?php
namespace Core;

use Closure;

class Event {

	protected $events = [];
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }


	public function trigger($name, array $args = [])
	{
		if ( ! isset($this->events[$name]) ) return;

		foreach ($this->events[$name] as $index => $callback) {
            if ( is_string($callback) && false !== strpos($callback, '.') ) {
                list($class, $method) = explode('.', $callback);

                $class = $this->container->get($class);
                return call_user_func_array([$class, $method], $args);
            }

            if ( is_array($callback) ) {
                return call_user_func_array($callback, $args);
            }

            if ( is_callable($callback) ) {
                return call_user_func_array($callback, $args);
            }

            throw new \Exception(
            	sprintf('Could not invoke callback for event [%s] at index [%d]', $name, $index)
            );
        }
	}

	public function on($name, $callback)
	{
		$this->events[$name][] = $callback;
		return $this;
	}

}