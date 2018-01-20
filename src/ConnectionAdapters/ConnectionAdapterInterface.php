<?php

namespace MicroSymfony\Connection\ConnectionAdapters;

interface ConnectionAdapterInterface
{
    /**
     * Send a raw request to given URL in the internet
     *
     * @param string $method
     * @param string $uri
     * @param array  $params
     * @return string
     */
    public function requestRaw(string $method, string $uri, array $params = []): string;

    /**
     * Send request to a given service and return the response
     * Wraps requestRaw with service-discovery covered solution
     *
     * @param string $method Any HTTP method, e.g. GET, POST
     * @param string $endpoint Should be in following format someService/someEndpoint
     * @param array  $params params to be passed to client, eg. ['body' => 'a=b', 'headers'=>['a' => 'b']]
     * @return string
     */
    public function request(string $method, string $endpoint, array $params = []): string;

    /**
     * Shortcut for request with GET method
     *
     * @param string $endpoint
     * @param array  $params
     * @return string
     */
    public function get(string $endpoint, array $params = []): string;

    /**
     * Shortcut for request with POST method
     *
     * @param string $endpoint
     * @param array  $params
     * @return string
     */
    public function post(string $endpoint, array $params = []): string;
}
