<?php
session_start();
define('DS', DIRECTORY_SEPARATOR);
define('WEB', __DIR__ . DS);
define('ROOT', dirname(WEB) . DS);
define('CORE', ROOT . 'core' . DS);
define('CONTENT', ROOT . 'content' . DS);
define('THEMES', ROOT . 'themes' . DS);
define('PLUGINS', ROOT . 'plugins' . DS);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// autoloading.
spl_autoload_register(function ($class) {
    require_once namespace2path($class) . '.php';
});

/**
 * get data from array using dot notation
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) {
        return $array;
    }

    if (isset($array[$key])) {
        return $array[$key];
    }

    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }

        $array = $array[$segment];
    }

    return $array;
}

/**
 * Set array key value using dot notation
 */
function array_set(&$array, $key, $value)
{
    if (is_null($key)) {
        return $array = $value;
    }

    $keys = explode('.', $key);
    while (count($keys) > 1) {
        $key = array_shift($keys);
        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = array();
        }
        $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;
    return $array;
}

/**
 * Combining keys and values.
 */
function combine($keys, $values)
{
    $data = [];
    if (count($keys) != count($values)) {
        return [];
    }

    foreach ($keys as $key => $value) {
        if (is_string($key)) {
            $data[$key] = combine($value, $values[$key]);
            continue;
        }
        $data[$value] = $values[$key];
    }

    return $data;
}

/**
 * Preg replace using key => array
 */
if (!function_exists('preg_replace_callback_array')) {

    function preg_replace_callback_array(array $patterns_and_callbacks, $subject, $limit = -1, &$count = null)
    {
        $count = 0;
        foreach ($patterns_and_callbacks as $pattern => &$callback) {
            $subject = preg_replace_callback($pattern, $callback, $subject, $limit, $partial_count);
            $count += $partial_count;
        }
        return preg_last_error() == PREG_NO_ERROR ? $subject : null;
    }

}

/**
 * Transforming an array to ini text.
 */
function arr2ini(array $a, array $parent = [])
{
    $out = '';
    foreach ($a as $k => $v) {
        if (is_array($v)) {

            $sec = array_merge((array) $parent, (array) $k);
            //add section information to the output
            $out .= "\n[" . join('.', $sec) . "]\n";
            //recursively traverse deeper
            $out .= arr2ini($v, $sec);
        } else {
            //plain key->value case
            if (preg_match('/\s/', $v)) {
                $v = "\"$v\"";
            }
            $out .= "$k=$v\n";
        }
    }
    return $out;
}

/**
 * Converting namespace to path
 */
function namespace2path($namespace)
{
    $path = str_replace(['Core\\', 'Plugins\\'], [CORE, PLUGINS], $namespace);
    return str_replace('\\', DS, $path);
}

/**
 * Load plugin config file if found.
 */
function get_plugin_config($plugin)
{
    $path = get_class($plugin);
    $file = dirname(namespace2path($path)) . DS . 'config.json';
    if (!file_exists($file)) {
        return [];
    }

    return json_decode(file_get_contents($file));
}


$config = json_decode(file_get_contents(ROOT . 'config.json'));
$error = false;
switch (json_last_error()) {
    case JSON_ERROR_DEPTH:
        $error = 'Maximum stack depth exceeded';
        break;
    case JSON_ERROR_STATE_MISMATCH:
        $error = 'Underflow or the modes mismatch';
        break;
    case JSON_ERROR_CTRL_CHAR:
        $error = 'Unexpected control character found';
        break;
    case JSON_ERROR_SYNTAX:
        $error = 'Syntax error, malformed JSON';
        break;
    case JSON_ERROR_UTF8:
        $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
}

if ($error != false) {
    throw new Exception($error);
}

$app = Core\App::instance();
$app->set('event', new Core\Event($app));

exit($app->run($config));
