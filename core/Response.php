<?php
namespace Core;

class Response
{
    /**
     * Response protocol
     * @var string
     */
    protected $protocol = 'HTTP/1.1';

    /**
     * Response body
     * @var string
     */
    protected $content = '';

    /**
     * Response headers
     * @var array
     */
    protected $headers = [];

    /**
     * Response status
     * @var integer
     */
    protected $status = 200;

    /**
     * Status message
     * @var string
     */
    protected $message = 'OK';

    /**
     * Statuses key and value.
     * @var array
     */
    protected $statuses = [
        200 => 'OK',
        301 => 'Redirect',
        404 => 'Not Found',
    ];

    /**
     * Setting up the response content
     * @param string
     */
    public function __construct($content = false)
    {
        $this->header('Content-Type', 'text/html');
        if ($content) {
            $this->content = $content;
        }
    }

    public function header($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set page status
     * @param  integer $status 
     * @return instance
     */
    public function status($status = 200)
    {
        if ( isset($this->statuses[$status]) ) {
            $this->status = $status;
            $this->message = $this->statuses[$status];
        }

        return $this;
    }

    /**
     * Registring redirect header
     * @param  string
     * @return this
     */
    public function redirect($url, $status = 301)
    {
        $this->headers['Location'] = $url;
        $this->status($status);
        return $this;
    }

    /**
     * Render response to request.
     * @return string
     */
    public function answer()
    {
        if (!headers_sent()) {
            header("$this->protocol $this->status $this->message");
            foreach ($this->headers as $key => $value) {
                header($key . ': ' . $value, true);
            }
        }

        return $this->content;
    }


    /**
     * Setting up page body.
     * @param  string
     * @return this
     */
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
