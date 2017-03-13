<?php
namespace Core;

class Response
{

	protected $protocol = 'HTTP/1.1';

	protected $content = '';
	protected $headers = [];

	protected $status  = 200;
	protected $message = 'OK';

	protected $statuses = [
		200	=>	'OK',
		301	=>	'Redirect',
		404 =>	'Not Found'
	];
	
	function __construct($content = false)
	{
		if ( $content ) {
			$this->content = $content;
		}
	}

	public function redirect($url)
	{
		$this->headers['Location'] = $url;
		return $this;
	}

	public function answer()
	{
		$this->headers['Content-Type'] = 'text/html';
		if ( ! headers_sent() ) {
			header("$this->protocol $this->status $this->message");
			foreach ($this->headers as $key => $value) {
				header($key . ': ' . $value, true);
			}
		}

		return $this->content;
	}

	public function content($content)
	{
		$this->content = $content;
		return $this;
	}

	public function __toString()
	{
		return $this->answer();
	}
}