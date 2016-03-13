<?php


namespace Filternet\Checkers;


class Http
{

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
            $this->fetchHttpStatus($url),
            $this->findTitle($response),
            $this->findStatus($response),
            date('Y-m-d H:i:s')
        ];
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
                'timeout' => 15,
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
        return @file_get_contents($url, false, $this->createContext());
    }

    /**
     * whether url is blocked
     *
     * @param string $html
     * @return bool
     */
    protected function blocked($html)
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

        return '<<UNKNOWN>>';
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
            return $this->blocked($response)
                ? $this->blockedText()
                : $this->openText();
        }

        return $this->unknownText();
    }

    /**
     * fetch http response status
     *
     * @param string $url
     * @return string
     */
    protected function fetchHttpStatus($url)
    {
        $headers = @get_headers($url);

        return count($headers) ? $headers[0] : $this->unknownText();
    }

    /**
     * Get blocked status text
     *
     * @return string
     */
    protected function blockedText()
    {
        return '<fg=red>Blocked</>';
    }

    /**
     * Get open status text
     *
     * @return string
     */
    protected function openText()
    {
        return '<fg=green>Open</>';
    }

    /**
     * Get unknown status text
     *
     * @return string
     */
    protected function unknownText()
    {
        return '<fg=blue>~UNKNOWN~</>';
    }
}