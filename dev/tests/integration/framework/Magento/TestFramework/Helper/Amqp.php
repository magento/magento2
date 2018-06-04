<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Helper;

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

    /**
     * Get All available connections
     *
     * @return array
     */
    public function getConnections()
    {
        $this->curl->get($this->host . 'connections');
        $data = $this->curl->getBody();
        $data = json_decode($data, true);
        $output = [];
        foreach ($data as $value) {
            $output[$value['name']] = $value;
        }
        return $output;
    }

    /**
     * @param string $name
     * @param int $numMessages
     * @return string
     */
    public function clearQueue(string $name, int $numMessages = 50)
    {
        $body = [
            "count" => $numMessages,
            "ackmode" => "ack_requeue_false",
            "encoding" => "auto",
            "truncate" => 50000
        ];
        $this->curl->post($this->host . 'queue/%2f/' . $name . '/get', json_encode($body));
        return $this->curl->getBody();
    }

    /**
     * Delete connection
     *
     * @param $name
     * @return string $data
     */
    public function deleteConnection($name)
    {
        $this->curl->delete($this->host . 'conections/' . urlencode($name));
        $data = $this->curl->getBody();
        return $data;
    }
}
