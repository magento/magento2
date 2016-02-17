<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http;

/**
 * Http static client
 */
class ClientStatic
{
    /**
     * @var Client
     */
    protected static $client;

    /**
     * Get the static HTTP client
     *
     * @param array|Traversable $options
     * @return Client
     */
    protected static function getStaticClient($options = null)
    {
        if (!isset(static::$client) || $options !== null) {
            static::$client = new Client(null, $options);
        }
        return static::$client;
    }

    /**
     * HTTP GET METHOD (static)
     *
     * @param  string $url
     * @param  array $query
     * @param  array $headers
     * @param  mixed $body
     * @param  array|Traversable $clientOptions
     * @return Response|bool
     */
    public static function get($url, $query = array(), $headers = array(), $body = null, $clientOptions = null)
    {
        if (empty($url)) {
            return false;
        }

        $request= new Request();
        $request->setUri($url);
        $request->setMethod(Request::METHOD_GET);

        if (!empty($query) && is_array($query)) {
            $request->getQuery()->fromArray($query);
        }

        if (!empty($headers) && is_array($headers)) {
            $request->getHeaders()->addHeaders($headers);
        }

        if (!empty($body)) {
            $request->setContent($body);
        }

        return static::getStaticClient($clientOptions)->send($request);
    }

    /**
     * HTTP POST METHOD (static)
     *
     * @param  string $url
     * @param  array $params
     * @param  array $headers
     * @param  mixed $body
     * @param  array|Traversable $clientOptions
     * @throws Exception\InvalidArgumentException
     * @return Response|bool
     */
    public static function post($url, $params, $headers = array(), $body = null, $clientOptions = null)
    {
        if (empty($url)) {
            return false;
        }

        $request= new Request();
        $request->setUri($url);
        $request->setMethod(Request::METHOD_POST);

        if (!empty($params) && is_array($params)) {
            $request->getPost()->fromArray($params);
        } else {
            throw new Exception\InvalidArgumentException('The array of post parameters is empty');
        }

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = Client::ENC_URLENCODED;
        }

        if (!empty($headers) && is_array($headers)) {
            $request->getHeaders()->addHeaders($headers);
        }

        if (!empty($body)) {
            $request->setContent($body);
        }

        return static::getStaticClient($clientOptions)->send($request);
    }
}
