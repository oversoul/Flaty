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
        if ($content) {
            $this->content = $content;
        }
    }

    /**
     * Registring redirect header
     * @param  string
     * @return this
     */
    public function redirect($url)
    {
        $this->headers['Location'] = $url;
        return $this;
    }

    /**
     * Render response to request.
     * @return string
     */
    public function answer()
    {
        $this->headers['Content-Type'] = 'text/html';
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
