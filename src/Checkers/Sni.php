<?php

namespace Filternet\Checkers;

/**
 * Class Sni
 * @package Filternet\Checkers
 */
class Sni
{
    /**
     * string
     *
     * @var string
     */
    private $domain;

    /**
     * Host to connect via TLS
     *
     * @var string
     */
    private $host = 'google.com';

    /**
     * Request Timeout
     *
     * @var int
     */
    private $timeout = 20;

    /**
     * Error string
     *
     * @var String
     */
    private $error;

    /**
     * whether domain is blocked
     *
     * @param $domain
     * @return bool
     */
    public function blocked($domain)
    {
        $this->domain = $domain;

        $context = $this->createContext();

        $client = $this->request($context);

        return $this->failed($client);
    }

    /**
     * Create a stream context
     *
     * @return resource
     */
    protected function createContext()
    {
        $options = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => false,
                'SNI_enabled' => true,
                'disable_compression' => false,
                'peer_name' => $this->domain,
                'capture_peer_cert' => true
            ]
        ];

        return stream_context_create($options);
    }

    /**
     * Send a tls request
     *
     * @param resource $context
     * @return resource
     */
    protected function request($context)
    {
        $this->error = null;

        set_error_handler(function ($errno, $errstr) {
            if ($this->error) {
                return;
            }

            $errorLines = explode("\n", $errstr);
            $this->error = ltrim(array_pop($errorLines), 'stream_socket_client() : ');
        });

        $client = stream_socket_client(
            "tls://{$this->host}:443", $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context
        );

        // remove previous error handler
        restore_error_handler();

        return $client;
    }

    /**
     * Is tls connection failed?
     *
     * @param resource $client
     * @return bool
     */
    protected function failed($client)
    {
        if (!is_resource($client) && $this->hasSslError($this->error)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = ltrim($host, 'https://');
    }

    /**
     * Get error
     *
     * @return string|null
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Set connection timeout
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Whether the error message is related to ssl/tls failures
     *
     * @param $error
     * @return int
     */
    protected function hasSslError($error)
    {
        return (preg_match('/ssl/ism', $error) || preg_match('/enable crypto/ism', $error))
            && !preg_match('/timed out/ism', $error)
    }

}
