<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

use \Magento\Framework\HTTP\ZendClient;

class CronEvent
{
    /**
     * @var \Magento\Framework\HTTP\ZendClient
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
     * @var \Magento\NewRelicReporting\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory $clientFactory
     */
    protected $clientFactory;

    /**
     * Constructor
     *
     * @param \Magento\NewRelicReporting\Model\Config $config
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\HTTP\ZendClientFactory $clientFactory
     */
    public function __construct(
        \Magento\NewRelicReporting\Model\Config $config,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\HTTP\ZendClientFactory $clientFactory
    ) {
        $this->config = $config;
        $this->jsonEncoder = $jsonEncoder;
        $this->clientFactory = $clientFactory;
    }

    /**
     * Returns Insights API url with account id
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getEventsUrl()
    {
        if (empty($this->eventsUrl)) {
            $accountId = $this->config->getNewRelicAccountId();
            if (empty($accountId)) {
                throw new \Magento\Framework\Exception\LocalizedException(__(
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
     * @return \Magento\Framework\HTTP\ZendClient
     */
    protected function getRequest()
    {
        if (!isset($this->request)) {
            $this->request = $this->clientFactory->create();
            $this->request->setUri($this->getEventsUrl());
            $insertKey = $this->config->getInsightsInsertKey();

            $this->request->setMethod(ZendClient::POST);
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
     * @return \Magento\NewRelicReporting\Model\CronEvent $this
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
        $response = $this->getRequest()
            ->setRawData($this->getJsonForResponse())
            ->request();

        if ($response->getStatus() >= 200 && $response->getStatus() < 300) {
            return true;
        }
        return false;
    }
}
