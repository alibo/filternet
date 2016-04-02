<?php


namespace Filternet\Checkers;


use Filternet\Utility\Status;

class Http
{

    /**
     * @var int
     */
    private $timeout = 15;

    private $headers;

    private $maxLength = 196;

    /**
     * Check status of url
     *
     * @param string $url
     * @return array
     */
    public function check($url)
    {
        $response = $this->request($url);

        return [
            $url,
            $this->fetchHttpStatus(),
            $this->findTitle($response),
            $this->findStatus($response),
            date('Y-m-d H:i:s')
        ];
    }

    /**
     * Is url blocked?
     *
     * @param string $url
     * @return bool
     */
    public function blocked($url)
    {
        $response = $this->request($url);

        return $this->isBlocked($response);
    }

    /**
     * Set timeout
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Create stream context
     *
     * @return resource
     */
    protected function createContext()
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'follow_location' => false,
                'ignore_errors' => true,
                'timeout' => $this->timeout,
                'user_agent' => 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'
            ],
        ];

        return stream_context_create($options);

    }

    /**
     * Send a http request
     *
     * @param string $url
     * @return string
     */
    protected function request($url)
    {
        $request = @file_get_contents($url, false, $this->createContext(), null, $this->maxLength);
        $this->headers = @$http_response_header;

        return $request;
    }

    /**
     * whether url is blocked
     *
     * @param string $html
     * @return bool
     */
    protected function isBlocked($html)
    {
        $titlePattern = '/<title>10\.10\.34\.3[4-6]<\/title>/ism';
        $iframePattern = '/<iframe.*?src="http:\/\/10\.10\.34\.3[4-6]/ism';

        return preg_match($titlePattern, $html) || preg_match($iframePattern, $html);
    }

    /**
     * Find title of page
     *
     * @param string $html
     * @return string
     */
    protected function findTitle($html)
    {
        $titlePattern = '/<title>(.*?)<\/title>/ism';

        if (preg_match($titlePattern, $html, $matches)) {
            return trim($matches[1]);
        }

        return Status::unknown();
    }

    /**
     * find status of url
     *
     * @param string|bool $response
     * @return string
     */
    protected function findStatus($response)
    {
        if ($response !== false) {
            return $this->isBlocked($response)
                ? Status::blocked()
                : Status::open();
        }

        return Status::unknown();
    }

    /**
     * fetch http response status
     *
     * @return string
     */
    protected function fetchHttpStatus()
    {
        return count($this->headers) ? $this->headers[0] : Status::unknown();
    }

    /**
     * Set maximum length in bytes
     *
     * @param int $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

}