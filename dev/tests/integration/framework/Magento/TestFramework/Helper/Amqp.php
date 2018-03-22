<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

use Magento\Framework\HTTP\Client\Curl;

/**
 * Helper class to access RabbitMQ server configuration
 */
class Amqp
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * RabbitMQ API host
     *
     * @var string
     */
    private $host = 'http://localhost:15672/api/';

    /**
     * Initialize dependencies.
     */
    public function __construct()
    {
        $this->curl = new Curl();
        $this->curl->setCredentials('guest', 'guest');
        $this->curl->addHeader('content-type', 'application/json');
    }

    /**
     * Get declared exchanges.
     *
     * @return array
     */
    public function getExchanges()
    {
        $this->curl->get($this->host . 'exchanges');
        $data = $this->curl->getBody();
        $data = json_decode($data, true);
        $output = [];
        foreach ($data as $value) {
            $output[$value['name']] = $value;
        }
        return $output;
    }

    /**
     * Get declared exchange bindings.
     *
     * @return array
     */
    public function getExchangeBindings($name)
    {
        $this->curl->get($this->host . 'exchanges/%2f/' . $name . '/bindings/source');
        $data = $this->curl->getBody();
        return json_decode($data, true);
    }
}
