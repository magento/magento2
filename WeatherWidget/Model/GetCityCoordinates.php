<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Tsg\WeatherWidgetApi\Api\Data\CoordinatesInterface;
use Tsg\WeatherWidgetApi\Api\Data\CoordinatesInterfaceFactory;
use Tsg\WeatherWidgetApi\Api\Data\WeatherWidgetConfigInterface;
use Tsg\WeatherWidgetApi\Api\GetCityCoordinatesInterface;
use Zend_Http_Client_Exception;
use Zend_Http_Response;

/**
 * Get city coordinates by city name.
 */
class GetCityCoordinates implements GetCityCoordinatesInterface
{
    private CoordinatesInterfaceFactory $coordinatesFactory;

    private WeatherWidgetConfigInterface $config;

    private ZendClientFactory $clientFactory;

    /**
     * @param CoordinatesInterfaceFactory $coordinatesFactory
     */
    public function __construct(
        CoordinatesInterfaceFactory $coordinatesFactory,
        WeatherWidgetConfigInterface $config,
        ZendClientFactory $clientFactory
    ) {
        $this->coordinatesFactory = $coordinatesFactory;
        $this->config = $config;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(): CoordinatesInterface
    {
        $response = $this->doRequest(
            $this->config->getApiUrl() . GetCityCoordinatesInterface::API_REQUEST_ENDPOINT,
            [
                'q' => $this->config->getCity(),
                'appid' => $this->config->getApiKey(),
            ]
        );

        if ($response->getStatus() !== 200) {
            throw new LocalizedException(__(
                'Could not fetch data from api call: %1',
                $response->getMessage()
            ));
        }
        $response = json_decode($response->getBody());

        return $this->coordinatesFactory->create([
            'longitude' => $response[0]->lon,
            'latitude' => $response[0]->lat,
        ]);
    }

    /**
     * Do API request with provided params.
     *
     * @param string $uriEndpoint
     * @param array $params
     *
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = []
    ): Zend_Http_Response {
        /** @var ZendClient $client */
        $client = $this->clientFactory->create();
        $client->setConfig(
            [
                'timeout' => 30,
            ]
        )->setParameterGet($params)
            ->setUri($uriEndpoint);

        return $client->request();
    }
}
