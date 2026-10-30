<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Helper;

/**
 * Helper class to access ActiveMq server configuration
 */
class Stomp
{
    public const CONFIG_PATH_HOST = 'queue/stomp/host';
    public const CONFIG_PATH_USER = 'queue/stomp/user';
    public const CONFIG_PATH_PASSWORD = 'queue/stomp/password';
    public const DEFAULT_MANAGEMENT_PROTOCOL = 'http';
    public const DEFAULT_MANAGEMENT_PORT = '8161';

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var string
     */
    private $host;

    /**
     * Initialize dependencies.
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ?\Magento\Framework\App\DeploymentConfig $deploymentConfig = null
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
            '%s://%s:%s/console/jolokia/',
            defined('ACTIVEMQ_MANAGEMENT_PROTOCOL')
                ? ACTIVEMQ_MANAGEMENT_PROTOCOL
                : self::DEFAULT_MANAGEMENT_PROTOCOL,
            $this->deploymentConfig->get(self::CONFIG_PATH_HOST),
            defined('ACTIVEMQ_MANAGEMENT_PORT') ? ACTIVEMQ_MANAGEMENT_PORT : self::DEFAULT_MANAGEMENT_PORT
        );
    }

    /**
     * Check that the ActiveMq instance has the JMX/Jalokia and the api is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $this->curl->get($this->host . 'version');
        $data = $this->curl->getBody();
        $data = json_decode($data, true);

        return isset($data['value']['agent']);
    }

    /**
     * Get declared queues.
     *
     * @return array
     */
    public function getQueues(): array
    {
        $body = [
            'type' => 'exec',
            'mbean' => 'org.apache.activemq.artemis:broker="0.0.0.0"',
            'operation' => 'listQueues(java.lang.String,int,int)',
            'arguments' => ["", 1, 100] // All queues, first page, 100 per page
        ];

        $json = json_encode($body);
        $this->curl->post($this->host, $json);
        $data = $this->curl->getBody();
        $data = json_decode($data, true);
        $output = [];
        if (isset($data['value'])) {
            $data = json_decode($data['value'], true);
            $data = $data['data'];
            foreach ($data as $value) {
                $output[$value['name']] = $value;
            }
        }
        return $output;
    }
}
