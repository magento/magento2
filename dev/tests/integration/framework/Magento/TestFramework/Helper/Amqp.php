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
    const CONFIG_PATH_HOST = 'queue/amqp/host';
    const CONFIG_PATH_USER = 'queue/amqp/user';
    const CONFIG_PATH_PASSWORD = 'queue/amqp/password';
    const DEFAULT_MANAGEMENT_PORT = '15672';

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * RabbitMQ API host
     *
     * @var string
     */
    private $host;

    /**
     * Initialize dependencies.
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig $deploymentConfig = null
    ) {
        $this->deploymentConfig = $deploymentConfig ?? \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\DeploymentConfig::class);
        $this->curl = new Curl();
        $this->curl->setCredentials(
            $this->deploymentConfig->get(self::CONFIG_PATH_USER),
            $this->deploymentConfig->get(self::CONFIG_PATH_PASSWORD)
        );
        $this->curl->addHeader('content-type', 'application/json');
        $this->host = sprintf(
            'http://%s:%s/api/',
            $this->deploymentConfig->get(self::CONFIG_PATH_HOST),
            defined('RABBITMQ_MANAGEMENT_PORT') ? RABBITMQ_MANAGEMENT_PORT : self::DEFAULT_MANAGEMENT_PORT
        );
    }

    /**
     * Check that the RabbitMQ instance has the management plugin installed and the api is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $this->curl->get($this->host . 'overview');
        $data = $this->curl->getBody();
        $data = json_decode($data, true);

        return isset($data['management_version']);
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
     * @param string $name
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
     * Clear Queue
     *
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
     * @param string $name
     * @return string $data
     */
    public function deleteConnection($name)
    {
        $this->curl->delete($this->host . 'conections/' . urlencode($name));
        $data = $this->curl->getBody();
        return $data;
    }
}
