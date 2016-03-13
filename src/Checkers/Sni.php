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
                'method' => 'GET',
                'follow_location' => false,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => false,
                'SNI_enabled' => true,
                'disable_compression' => false,
                'peer_name' => $this->domain,
                'capture_peer_cert' => true,
                'capture_peer_cert_chain' => true,
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
        return @file_get_contents('https://google.com', false, $context);
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
}