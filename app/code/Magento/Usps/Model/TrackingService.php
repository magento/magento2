<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Shipping\Model\Tracking\Result;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Throwable;

class TrackingService
{
    public const TRACK_REQUEST_END_POINT = 'tracking/v3/tracking';
    public const CONTENT_TYPE_JSON = 'application/json';
    public const AUTHORIZATION_BEARER = 'Bearer ';
    private const ERROR_TITLE_DEFAULT = 'For some reason we can\'t retrieve tracking info right now.';

    /**
     * @var ResultFactory
     */
    private $trackFactory;

    /**
     * Rate result data
     *
     * @var Result|null
     */
    private $result = null;

    /**
     * @var ErrorFactory
     */
    private $trackErrorFactory;

    /**
     * @var StatusFactory
     */
    private $trackStatusFactory;

    /**
     * @var AsyncClientInterface
     */
    private $httpClient;

    /**
     * @var Carrier
     */
    private Carrier $carrierModel;

    /**
     * TrackingService constructor.
     *
     * @param ResultFactory $trackFactory
     * @param ErrorFactory $trackErrorFactory
     * @param StatusFactory $trackStatusFactory
     * @param AsyncClientInterface|null $httpClient
     */
    public function __construct(
        ResultFactory $trackFactory,
        ErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        ?AsyncClientInterface $httpClient = null
    ) {
        $this->trackFactory = $trackFactory;
        $this->trackErrorFactory = $trackErrorFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->httpClient = $httpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
    }

    /**
     * Set carrier model
     *
     * @param Carrier $carrierModel
     * @return void
     */
    public function setCarrierModel(Carrier $carrierModel) : void
    {
        $this->carrierModel = $carrierModel;
    }

    /**
     * Send request for tracking using REST API
     *
     * @param array $trackingData
     * @return Result|null
     * @throws LocalizedException
     * @throws Throwable
     */
    public function getRestTracking(array $trackingData): ?Result
    {
        $url = $this->carrierModel->getUrl(self::TRACK_REQUEST_END_POINT);
        $accessToken = $this->carrierModel->getOauthAccessRequest();
        $queryParams = [
            "expand" => "DETAIL"
        ];

        /** @var HttpResponseDeferredInterface[] $trackingResponses */
        $trackingResponses = [];
        $debugData = [];

        foreach ($trackingData as $tracking) {
            if ($tracking === null) {
                $tracking = '';
            }
            $trackParams = (object)[];
            $trackPayload = json_encode($trackParams);
            $headers = [
                'Content-Type' => self::CONTENT_TYPE_JSON,
                'Authorization' => self::AUTHORIZATION_BEARER . $accessToken
            ];
            $debugData[$tracking] = ['request' => $trackPayload];
            try {
                $trackingResponses[$tracking] = $this->httpClient->request(
                    new Request(
                        $url . '/' . urlencode($tracking) .
                        "?" . http_build_query($queryParams),
                        Request::METHOD_GET,
                        $headers,
                        null
                    )
                );
            } catch (\Throwable $e) {
                $debugData[$tracking]['error'] = $e->getMessage();
                $this->carrierModel->_debug($debugData);
                continue; // Skip to the next tracking number
            }
        }
        foreach ($trackingResponses as $tracking => $response) {
            $httpResponse = $response->get();
            $jsonResponse = $httpResponse->getStatusCode() >= 400 ? '' : $httpResponse->getBody();
            $debugData[$tracking]['result'] = $jsonResponse;
            $this->carrierModel->_debug($debugData);
            $this->parseRestTrackingResponse((string)$tracking, $jsonResponse);
        }
        return $this->result;
    }

    /**
     * Parse REST tracking response
     *
     * @param string $trackingValue
     * @param string $jsonResponse
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function parseRestTrackingResponse(string $trackingValue, string $jsonResponse): Result
    {
        $errorTitle = self::ERROR_TITLE_DEFAULT;
        $resultArr = [];
        $packageProgress = [];
        if ($jsonResponse) {
            $responseData = json_decode($jsonResponse, true);
            if (is_array($responseData) && isset($responseData['trackingNumber'])) {
                $trackingEvents = $responseData['trackingEvents'] ?? [];
                if (is_array($trackingEvents)) {
                    foreach ($trackingEvents as $activityTag) {
                        $this->processActivityRestTagInfo($activityTag, $packageProgress);
                    }
                    $resultArr['track_summary'] = $responseData['statusSummary'];
                    $resultArr['progressdetail'] = $packageProgress;
                }
            } else {
                $errorTitle = $responseData['error']['message'];
            }
        }

        return $this->setTrackingResultData($resultArr, $trackingValue, $errorTitle);
    }

    /**
     * Set Tracking Response Data
     *
     * @param array $resultArr
     * @param string $trackingValue
     * @param string $errorTitle
     * @return Result
     */
    private function setTrackingResultData(array $resultArr, string $trackingValue, string $errorTitle): Result
    {
        if (!$this->result) {
            $this->result = $this->trackFactory->create();
        }

        if ($resultArr) {
            $tracking = $this->trackStatusFactory->create();
            $tracking->setCarrier($this->carrierModel->_code);
            $tracking->setCarrierTitle($this->carrierModel->getConfigData('title'));
            $tracking->setTracking($trackingValue);
            $tracking->addData($resultArr);
            $this->result->append($tracking);
        } else {
            $error = $this->trackErrorFactory->create();
            $error->setCarrier($this->carrierModel->_code);
            $error->setCarrierTitle($this->carrierModel->getConfigData('title'));
            $error->setTracking($trackingValue);
            $error->setErrorMessage($errorTitle);
            $this->result->append($error);
        }

        return $this->result;
    }

    /**
     * Process tracking info from activity tag
     *
     * @param array $activityTag
     * @param array $packageProgress
     */
    private function processActivityRestTagInfo(
        array $activityTag,
        array &$packageProgress
    ): void {
        $addressArr = array_filter([
            $activityTag['eventCity'] ?? null,
            $activityTag['eventState'] ?? null,
            $activityTag['eventCountry'] ?? null
        ]);

        $eventTimestamp = (new \DateTime((string)$activityTag['eventTimestamp']));
        $date = $eventTimestamp->format('Y-m-d');
        $time = $eventTimestamp->format('H:i:s');

        $packageProgress[] = [
            'activity' => (string)$activityTag['eventType'],
            'deliverydate' => $date,
            'deliverytime' => $time,
            'deliverylocation' => implode(', ', $addressArr)
        ];
    }

    /**
     * Get tracking response
     *
     * @return string
     */
    public function getResponse(): string
    {
        $statuses = '';
        if ($this->result instanceof \Magento\Shipping\Model\Tracking\Result) {
            if ($trackingData = $this->result->getAllTrackings()) {
                foreach ($trackingData as $tracking) {
                    if ($data = $tracking->getAllData()) {
                        $statuses .= !empty($data['track_summary']) ? __($data['track_summary']) : __('Empty response');
                    }
                }
            }
        }
        return $statuses ?: (string)__('Empty response');
    }
}
