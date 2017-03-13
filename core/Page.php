<?php
namespace Core;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Page
{
	protected $path;
	protected $data = [];
	protected $content = '';

	function loadable($path)
	{
		if ( file_exists($path) and is_readable($path) ) {
			$this->path = $path;
			return true;
		}
		return false;
	}

	public static function all($path)
	{
		return new RecursiveIteratorIterator( 
			new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST,
			RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
		);
	}

	public function load()
	{
		$file = trim(file_get_contents($this->path));

		if ( $file == '' ) return;

		$all = explode('---', $file, 2);

		$this->parseHeader($all[0]);
		
		if ( count($all) == 1 ) return;

		$this->parseContent($all[1]);
	}

	public function resetHeader($data)
	{
		$this->data = $data;
	}

	public function loadHeader()
	{
		if ( $this->content == '' ) {
			$f = fopen($this->path, 'r');
			$lines = [];
			while ( ! feof($f) ) {
				$line = trim( fgets($f) );
				if ( $line == '---' ) break;
				$lines[] = $line;
			}

			$this->parseHeader(implode("\n", $lines));
		}
	}

	public function parseHeader($header)
	{
		$this->data = $this->data + parse_ini_string($header, true);
	}

	public function parseContent($content)
	{
		$this->content = $content;
	}

	public function setHeader(array $data)
	{
		$this->data = $this->data + $data;
	}

	public function setContent($content)
	{
		$this->content = $content;
	}

	public function header()
	{
		return $this->data;
	}

	public function content()
	{
		return $this->content;
	}

	public function fillRaw($header, $data, $sep = "---")
	{
		if ( ! $this->path ) return false;

		return file_put_contents($this->path, $header . $sep . "\n" . $data);
	}

	public function create($path)
	{
		if ( file_put_contents($path, '') ) {
			$this->path = $path;
			return true;
		}
		return false;
	}

	public function remove()
	{
		return unlink($this->path);
	}
}