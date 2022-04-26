<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model;

use Magento\Framework\Exception\LocalizedException;
use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;
use Tsg\WeatherWidgetApi\Api\Data\RecordInterfaceFactory;
use Tsg\WeatherWidgetApi\Api\Data\WeatherWidgetConfigInterface;
use Tsg\WeatherWidgetApi\Api\GetCityCoordinatesInterface;
use Tsg\WeatherWidgetApi\Api\GetCityWeatherInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Tsg\WeatherWidgetApi\Api\RecordRepositoryInterface;
use Zend_Http_Response;

/**
 * Get weather data by city.
 */
class GetCityWeather implements GetCityWeatherInterface
{
    private const URI_ENDPOINT = 'data/2.5/weather';

    private WeatherWidgetConfigInterface $config;

    private GetCityCoordinatesInterface $getCityCoordinates;

    private RecordInterfaceFactory $recordFactory;

    private ZendClientFactory $clientFactory;

    private RecordRepositoryInterface $recordRepository;

    /**
     * @param WeatherWidgetConfigInterface $config
     * @param GetCityCoordinatesInterface $getCityCoordinates
     * @param RecordInterfaceFactory $recordFactory
     * @param ZendClientFactory $clientFactory
     * @param RecordRepositoryInterface $recordRepository
     */
    public function __construct(
        WeatherWidgetConfigInterface $config,
        GetCityCoordinatesInterface $getCityCoordinates,
        RecordInterfaceFactory $recordFactory,
        ZendClientFactory $clientFactory,
        RecordRepositoryInterface $recordRepository
    ) {
        $this->config = $config;
        $this->getCityCoordinates = $getCityCoordinates;
        $this->recordFactory = $recordFactory;
        $this->clientFactory = $clientFactory;
        $this->recordRepository = $recordRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        /** @var RecordInterface $record */
        $record = $this->recordFactory->create();
        $coordinates = $this->getCityCoordinates->execute();

        $response = $this->doRequest(
            $this->config->getApiUrl() . GetCityWeatherInterface::API_REQUEST_ENDPOINT,
            [
                'lat' => $coordinates->getLatitude(),
                'lon' => $coordinates->getLatitude(),
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

        $record->setCity($this->config->getCity())
            ->setTemperature($response->main->temp);

        $this->recordRepository->save($record);
    }

    /**
     * Do API request with provided params.
     *
     * @param string $uriEndpoint
     * @param array $params
     *
     * @return Zend_Http_Response
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
