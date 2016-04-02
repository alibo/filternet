<?php

namespace Filternet\Checkers;

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
    private $host = 'https://google.com';

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

        $this->request($context);

        return $this->failed($context);
    }

    /**
     * Create a stream context
     *
     * @return resource
     */
    protected function createContext()
    {
        $options = [
            'http' => [
                'method' => 'HEAD',
                'follow_location' => false,
                'user_agent' => 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => false,
                'SNI_enabled' => true,
                'disable_compression' => false,
                'peer_name' => $this->domain,
                'capture_peer_cert' => false,
                'capture_peer_cert_chain' => false,
                'capture_session_meta' => true,
            ],
        ];

        return stream_context_create($options);
    }

    /**
     * Send a tls request
     *
     * @param resource $context
     * @return string
     */
    protected function request($context)
    {
        return @file_get_contents($this->host, false, $context , null, 0);
    }

    /**
     * Is tls connection failed?
     *
     * @param resource $context
     * @return bool
     */
    protected function failed($context)
    {
        $meta = stream_context_get_options($context);

        return !isset($meta['ssl']['session_meta']['protocol']);
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = 'https://' . ltrim($host, 'https://');
    }
}