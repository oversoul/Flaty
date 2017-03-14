<?php
namespace Core;

class Request
{

    /**
     * Server data
     * @var array
     */
    protected $server = [];

    /**
     * Post data
     * @var array
     */
    protected $input = [];

    /**
     * Get data
     * @var array
     */
    protected $query = [];

    /**
     * Setting up global variables data.
     */
    public function __construct()
    {
        $this->server = $_SERVER;
        $this->input = $_POST;
        $this->query = $_GET;
    }

    /**
     * Get data from server.
     * @param  string
     * @return string
     */
    public function server($key = false)
    {
        if (!$key) {
            return $this->server;
        }

        return array_get($this->server, $key, false);
    }

    /**
     * Get data from POST.
     * @param  string
     * @return string
     */
    public function post($keys = false)
    {
        if (!$keys) {
            return $this->input;
        }

        return array_get($this->input, $keys, false);
    }

    /**
     * Get data from GET.
     * @param  string
     * @return string
     */
    public function query($keys = false)
    {
        return array_get($this->query, $keys, false);
    }

    /**
     * Get page request method
     * @return string
     */
    public function method()
    {
        if ($this->post('_method')) {
            return $this->post('_method');
        }

        return $this->server('REQUEST_METHOD');
    }

    /**
     * Get current page uri.
     * @return string
     */
    public function uri()
    {
        return rtrim($this->server('REQUEST_URI'), '/');
    }
}
