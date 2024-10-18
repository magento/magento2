<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

use Laminas\Http\Request;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\Helper\Data;

class CronEvent
{
    /**
     * @var LaminasClient
     */
    protected $request;

    /**
     * URL for Insights API, generated via method getEventsUrl
     *
     * @var string
     */
    protected $eventsUrl = '';

    /**
     * Parameters to be sent to New Relic for a job run
     *
     * @var array
     */
    protected $customParameters = [];

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Data
     */
    protected $jsonEncoder;

    /**
     * @var LaminasClientFactory $clientFactory
     */
    protected $clientFactory;

    /**
     * Constructor
     *
     * @param Config $config
     * @param EncoderInterface $jsonEncoder
     * @param LaminasClientFactory $clientFactory
     */
    public function __construct(
        Config $config,
        EncoderInterface $jsonEncoder,
        LaminasClientFactory $clientFactory
    ) {
        $this->config = $config;
        $this->jsonEncoder = $jsonEncoder;
        $this->clientFactory = $clientFactory;
    }

    /**
     * Returns Insights API url with account id
     *
     * @return string
     * @throws LocalizedException
     */
    protected function getEventsUrl()
    {
        if (empty($this->eventsUrl)) {
            $accountId = $this->config->getNewRelicAccountId();
            if (empty($accountId)) {
                throw new LocalizedException(__(
                    'No New Relic Application ID configured, cannot continue with Cron Event reporting'
                ));
            }
            $this->eventsUrl = sprintf(
                $this->config->getInsightsApiUrl(),
                $accountId
            );
        }
        return $this->eventsUrl;
    }

    /**
     * Returns HTTP request to events url
     *
     * @return LaminasClient
     */
    protected function getRequest()
    {
        if (!isset($this->request)) {
            $this->request = $this->clientFactory->create();
            $this->request->setUri($this->getEventsUrl());
            $insertKey = $this->config->getInsightsInsertKey();

            $this->request->setMethod(Request::METHOD_POST);
            $this->request->setHeaders(
                [
                    'X-Insert-Key' => $insertKey,
                    'Content-Type' => 'application/json',
                ]
            );
        }
        return $this->request;
    }

    /**
     * Returns all set custom parameters as JSON string
     *
     * @return string
     */
    protected function getJsonForResponse()
    {
        $json = [
            'eventType' => 'Cron',
            'appName'   => $this->config->getNewRelicAppName(),
            'appId'     => $this->config->getNewRelicAppId(),
        ];
        $jsonArrayKeys = array_keys($json);

        foreach ($jsonArrayKeys as $jsonKey) {
            if (array_key_exists($jsonKey, $this->customParameters)) {
                unset($this->customParameters[$jsonKey]);
            }
        }

        $json = array_merge($json, $this->customParameters);

        return $this->jsonEncoder->encode($json);
    }

    /**
     * Add custom parameters to send with API request
     *
     * @param array $data
     * @return CronEvent $this
     */
    public function addData(array $data)
    {
        $this->customParameters = array_merge($this->customParameters, $data);
        return $this;
    }

    /**
     * Instantiates request if necessary and sends off with collected data
     *
     * @return bool
     */
    public function sendRequest()
    {
        $this->getRequest()->setRawBody($this->getJsonForResponse());
        $response = $this->getRequest()->send();
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return true;
        }
        return false;
    }
}
