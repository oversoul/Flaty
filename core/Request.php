<?php
namespace Core;

class Request
{

	protected $server = [];
	protected $input  = [];
	protected $query  = [];

	function __construct()
	{
		$this->server = $_SERVER;
		$this->input  = $_POST;
		$this->query  = $_GET;
	}

	public function server($key = false)
	{
		if ( ! $key ) return $this->server;
		return array_get($this->server, $key, false);
	}

	public function post($keys = false)
	{
		if ( ! $keys ) return $this->input;
		return array_get($this->input, $keys, false);
	}

	public function query($keys = false)
	{
		return array_get($this->query, $keys, false);
	}

	public function method()
	{
		if ( isset($this->input['_method']) ) return $this->input['_method'];
		return $this->server['REQUEST_METHOD'];
	}

	public function uri()
	{
		return $this->server['REQUEST_URI'];
	}
}