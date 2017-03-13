<?php
namespace Core;

use Exception;

class View
{

    /**
     * @var internal variables
     */
    protected $vars = [];

    /**
     * @var view extension
     */
    protected $ext = '.tpl';

    /**
     * @var directory
     */
    protected $directory;

    /**
     * set directory folder
     * @param directory
     */
    public function __construct($directory = THEMES)
    {
        $this->directory = $directory;
    }

    /**
     * decode pseudo code
     * @param string
     * @return string
     */
    public function compile($content)
    {
        $keys = [
            "{% for %% %}" => [$this, 'parseForEach'],
            "{% endfor %}" => [$this, 'parseEndFor'],
            "{% if %% %}" => [$this, 'parseIf'],
            "{% else %}" => [$this, 'parseElse'],
            "{% endif %}" => [$this, 'parseEndif'],
            "{% include %% %}" => [$this, 'parseInclude'],
            "{% set %% = %% %}" => [$this, 'parseSet'],
            "{{ %% }}" => [$this, 'parseEcho'],
            // "{#(\n.*)+#}"        =>    [$this, 'parseComments']
        ];

        foreach ($keys as $key => $val) {
            unset($keys[$key]);
            $keys['#' . str_replace('%%', '(.+)', preg_quote($key, '#')) . '#U'] = $val;
        }

        $keys["~\{#(\n.*)+#\}~"] = [$this, 'parseComments'];

        return preg_replace_callback_array($keys, $content);
    }

    /**
     * Parse foreach
     * @param string
     * @return string
     */
    public function parseForEach($key)
    {
        array_shift($key);
        list($key, $p, $var) = explode(' ', $key[0]);

        return "<?php if (! empty(\$this->get(\"{$var}\"))) foreach (\$this->get(\"{$var}\") as \$key => \$$key):
			\$this->set(\"$key\", \$$key);
			\$this->set(\"index\", \$key); ?>";
    }

    /**
     * Parse if
     * @param string
     * @return string
     */
    public function parseIf($key)
    {
        array_shift($key);
        $keys = '(' . str_replace([' and ', ' or '], [') and (', ') or ('], $key[0]) . ')';

        $keys = preg_replace_callback('#\(([\'"a-z._]+)([is|is not]+)?( [^)]+)?\)#', function ($statement) {
            array_shift($statement);
            if (count($statement) == 1) {
                return "\$this->get(\"{$statement[0]}\")";
            }

            list($first, $op, $second) = $statement;
            $op = trim($op);
            $first = trim($first);
            $second = trim($second);
            if (substr($first, -1) != "\"" and substr($first, -1) != "'") {
                $first = "\$this->get(\"{$first}\")";
            }

            if (in_array($second, ['array', 'object', 'null', 'string', 'numeric'])) {
                $op = ($op == 'is') ? '' : '!';
                return "{$op} is_{$second}({$first})";
            }

            if (in_array($second, ['empty'])) {
                $op = ($op == 'is') ? '' : '!';
                return "{$op} {$second}({$first})";
            }

            $op = ($op == 'is') ? '==' : '!=';

            if (substr($second, -1) != "\"" and substr($second, -1) != "'") {
                $second = "\$this->get(\"{$second}\")";
            }

            return "{$first} {$op} {$second}";

        }, $keys);

        return "<?php if (" . $keys . "): ?>";
    }

    /**
     * Parse endif
     * @param string
     * @return string
     */
    public function parseEndif()
    {
        return "<?php endif; ?>";
    }

    /**
     * Parse else
     * @param string
     * @return string
     */
    public function parseElse()
    {
        return "<?php else: ?>";
    }

    /**
     * Parse set
     * @param string
     * @return string
     */
    public function parseSet($key)
    {
        array_shift($key);
        list($var, $val) = $key;
        return "<?php \$this->set(\"$var\", $val); ?>";
    }

    /**
     * Parse endforeach
     * @param string
     * @return string
     */
    public function parseEndFor()
    {
        return "<?php endforeach; ?>";
    }

    /**
     * Parse echo
     * @param string
     * @return string
     */
    public function parseEcho($key)
    {
        array_shift($key);
        $key = $key[0];
        $keys = explode('|', $key);
        if (count($keys) > 1) {
            list($key, $parser) = $keys;
            return "<?= $parser(\$this->get(\"$key\")); ?>";
        }

        return "<?= \$this->get(\"$keys[0]\"); ?>";
    }

    /**
     * Parse comments
     * @param string
     * @return string
     */
    public function parseComments($key)
    {
        return "<?php /* {$key[0]} */ ?>";
    }

    /**
     * Parse include
     * @param string
     * @return string
     */
    public function parseInclude($key)
    {
        array_shift($key);
        $file = trim(trim($key[0], "'"), '"');
        $file = $this->directory . str_replace('.', DS, $file) . $this->ext;
        if (!file_exists($file)) {
            throw new \Exception("Template file [$file] does not exists.");
        }

        $content = file_get_contents($file);
        return $this->evaluate($content);
    }

    /**
     * run eval function on content
     * @param string
     * @return string
     */
    public function evaluate($content)
    {
        $content = $this->compile($content);
        // return $content;
        ob_start();
        eval('?>' . $content);

        return ob_get_clean();
    }

    /**
     * render block of code
     * @param string
     * @param string
     * @return string
     */
    public function renderBlock($name, $content)
    {
        $content = $this->evaluate($content);
        // $content = $this->compile($content);
        $this->set("block." . $name, $content);
    }

    /**
     * Render code.
     * @param file name
     * @return string
     */
    public function render($file)
    {
        $file = $this->directory . $file . $this->ext;

        if (!file_exists($file)) {
            throw new \Exception("Template file [$file] does not exists.");
        }

        $content = file_get_contents($file);
        return $this->evaluate($content);
    }

    /**
     * Set key value
     * @param string
     * @param string/optional
     */
    public function set($key, $value = false)
    {
        if ($value === false) {
            $this->vars = $this->vars + $key;
            return;
        }
        return array_set($this->vars, $key, $value);
    }

    /**
     * get data from vars
     * @param string
     * @return string / boolean
     */
    public function get($key, $default = false)
    {
        return array_get($this->vars, $key, $default);
    }
}
