<?php
namespace Core;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Page
{
    /**
     * File path
     * @var string
     */
    protected $path;

    /**
     * Page header data
     * @var array
     */
    protected $data = [];

    /**
     * content of page
     * @var string
     */
    protected $content = '';

    /**
     * Check if file is loadable
     * @param  string
     * @return boolean
     */
    public function loadable($path)
    {
        if (file_exists($path) and is_readable($path)) {
            $this->path = $path;
            return true;
        }
        return false;
    }

    /**
     * Get all files recursivly.
     * @param  string
     * @return Iterator
     */
    public static function all($path)
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD// Ignore "Permission denied"
        );
    }

    /**
     * Get content of a file.
     * @return
     */
    public function load()
    {
        if (!$this->path) {
            return false;
        }
        
        $file = trim(file_get_contents($this->path));

        if ($file == '') {
            return;
        }

        $all = explode('---', $file, 2);

        $this->parseHeader($all[0]);

        if (count($all) == 1) {
            return;
        }

        $this->parseContent($all[1]);
    }

    /**
     * Reset data header.
     * @param  array
     * @return
     */
    public function resetHeader($data)
    {
        $this->data = $data;
    }

    /**
     * Load header only.
     * @return
     */
    public function loadHeader()
    {
        if ($this->content == '') {
            $f = fopen($this->path, 'r');
            $lines = [];
            while (!feof($f)) {
                $line = trim(fgets($f));
                if ($line == '---') {
                    break;
                }

                $lines[] = $line;
            }

            $this->parseHeader(implode("\n", $lines));
        }
    }

    /**
     * Parse header data and append it to data.
     * @param  string
     */
    public function parseHeader($header)
    {
        $this->data = $this->data + parse_ini_string($header, true);
    }

    /**
     * Parse Content and setting it up
     *
     * can later have markdown parser.
     * @param  string
     */
    public function parseContent($content)
    {
        $this->content = $content;
    }

    /**
     * Append data array to data.
     * @param array
     */
    public function setHeader(array $data)
    {
        $this->data = $this->data + $data;
    }

    /**
     * Setting the content.
     * @param string
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get parsed header data
     * @return array
     */
    public function header()
    {
        return $this->data;
    }

    /**
     * Get the page content.
     * @return string
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * Saing content to a file.
     * @param  string
     * @param  string
     * @param  string
     * @return boolean
     */
    public function fillRaw($header, $data, $sep = "---")
    {
        if (!$this->path) {
            return false;
        }

        return file_put_contents($this->path, $header . $sep . "\n" . $data);
    }

    /**
     * Create an empty file
     * @param  string
     * @return boolean
     */
    public function create($path)
    {
        if (file_put_contents($path, '')) {
            $this->path = $path;
            return true;
        }
        return false;
    }

    /**
     * Deleting a file.
     * @return boolean
     */
    public function remove()
    {
        return unlink($this->path);
    }
}
