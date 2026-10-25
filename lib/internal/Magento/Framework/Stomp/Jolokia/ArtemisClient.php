<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Jolokia;

use Exception;
use JsonException;
use Magento\Framework\HTTP\ClientInterface as HttpClient;
use Magento\Framework\Stomp\Config;
use Psr\Log\LoggerInterface;

class ArtemisClient implements ClientInterface
{
    private const int DEFAULT_PORT = 8161;

    /**
     * @param Config $stompConfig
     * @param HttpClient $httpClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $stompConfig,
        private readonly HttpClient $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        $params = ['type' => 'version'];
        try {
            $data = $this->executeRequest($params);
            $result = isset($data['value']['agent']);
        } catch (RequestFailedException $e) {
            $this->logger->notice($e);
            $result = false;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function clearQueue(string $queueName): int
    {
        $mBean = [
            'org.apache.activemq.artemis:broker="0.0.0.0"',
            'component=addresses',
            'address="' . $queueName . '"',
            'subcomponent=queues',
            'routing-type="anycast"',
            'queue="' . $queueName . '"',
        ];
        $params = [
            'type' => 'exec',
            'mbean' => implode(',', $mBean),
            'operation' => 'removeAllMessages()',
            'arguments' => [],
        ];
        $response = $this->executeRequest($params);
        $clearedCount = (int) ($response['value'] ?? 0);
        $this->logger->info("Removed $clearedCount messages from '$queueName' queue via Jolokia API.");

        return $clearedCount;
    }

    /**
     * @inheritdoc
     */
    public function getQueueMessageCount(string $queueName): int
    {
        $mBean = [
            'org.apache.activemq.artemis:broker="0.0.0.0"',
            'component=addresses',
            'address="' . $queueName . '"',
            'subcomponent=queues',
            'routing-type="anycast"',
            'queue="' . $queueName . '"',
        ];
        $params = [
            'type' => 'read',
            'mbean' => implode(',', $mBean),
            'attribute' => 'MessageCount',
        ];
        $response = $this->executeRequest($params);
        $count = (int) $response['value'];

        return $count;
    }

    /**
     * Execute Jolokia request to Artemis management interface
     *
     * @param array $params
     * @return array
     * @throws RequestFailedException
     */
    private function executeRequest(array $params): array
    {
        $host = $this->stompConfig->getValue(Config::HOST);
        $user = $this->stompConfig->getValue(Config::USERNAME);
        $password = $this->stompConfig->getValue(Config::PASSWORD);
        $url = 'http://' . $host . ':' . self::DEFAULT_PORT . '/console/jolokia/';

        $this->httpClient->setCredentials($user, $password);
        $this->httpClient->addHeader('Content-Type', 'application/json');
        try {
            $this->httpClient->post($url, json_encode($params));
        } catch (Exception $e) {
            throw new RequestFailedException('Unable to make HTTP request to Jolokia API.', previous: $e);
        }

        $status = $this->httpClient->getStatus();
        if ($status !== 200) {
            throw new RequestFailedException('HTTP request to Jolokia API failed.', $status);
        }

        $response = $this->httpClient->getBody();
        try {
            $data = json_decode($response, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RequestFailedException("Invalid JSON response from Jolokia API: '$response'.", $status);
        }

        if (isset($data['status']) && $data['status'] !== 200) {
            $errorMessage = $data['error'] ?? 'Unknown error';
            throw new RequestFailedException("Jolokia error: '$errorMessage'.", (int) $data['status']);
        }

        return $data;
    }
}
