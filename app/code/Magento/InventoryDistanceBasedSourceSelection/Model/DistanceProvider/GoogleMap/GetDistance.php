<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GoogleMap;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GetDistanceInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\Request\LatLngRequestInterface;

/**
 * @inheritdoc
 */
class GetDistance implements GetDistanceInterface
{
    private const GOOGLE_ENDPOINT = 'https://maps.googleapis.com/maps/api/distancematrix/json';
    private const XML_PATH_MODE = 'cataloginventory/source_selection_distance_based_google/mode';
    private const XML_PATH_VALUE = 'cataloginventory/source_selection_distance_based_google/value';

    /**
     * @var array
     */
    private $distanceCache = [];

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var GetApiKey
     */
    private $getApiKey;

    /**
     * GetLatLngFromAddress constructor.
     *
     * @param ClientInterface $client
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param GetApiKey $getApiKey
     */
    public function __construct(
        ClientInterface $client,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        GetApiKey $getApiKey
    ) {
        $this->client = $client;
        $this->json = $json;
        $this->getApiKey = $getApiKey;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute(LatLngRequestInterface $source, LatLngRequestInterface $destination): float
    {
        $key = $source->getAsString() . '|' . $destination->getAsString();

        if (!isset($this->distanceCache[$key])) {
            $queryString = http_build_query([
                'key' => $this->getApiKey->execute(),
                'origins' => $source->getAsString(),
                'destinations' => $destination->getAsString(),
                'mode' => $this->scopeConfig->getValue(self::XML_PATH_MODE),
            ]);

            $this->client->get(self::GOOGLE_ENDPOINT . '?' . $queryString);
            if ($this->client->getStatus() !== 200) {
                throw new LocalizedException(__('Unable to connect google API for distance matrix'));
            }

            $res = $this->json->unserialize($this->client->getBody());

            if ($res['status'] !== 'OK') {
                throw new LocalizedException(
                    __(
                        'Unable to get distance between %1 and %2',
                        $source->getAsString(),
                        $destination->getAsString()
                    )
                );
            }

            $element = $res['rows'][0]['elements'][0];

            if ($this->scopeConfig->getValue(self::XML_PATH_VALUE) === 'time') {
                $this->distanceCache[$key] = (float)$element['duration']['value'];
            } else {
                $this->distanceCache[$key] = (float)$element['distance']['value'];
            }
        }

        return $this->distanceCache[$key];
    }
}
