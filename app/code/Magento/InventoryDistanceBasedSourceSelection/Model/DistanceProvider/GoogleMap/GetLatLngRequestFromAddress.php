<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GoogleMap;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GetLatLngRequestFromAddressInterface;
use Magento\InventoryDistanceBasedSourceSelection\Model\Request\Convert\AddressRequestToComponentsString;
use Magento\InventoryDistanceBasedSourceSelection\Model\Request\Convert\AddressRequestToQueryString;
use Magento\InventoryDistanceBasedSourceSelection\Model\Request\Convert\AddressRequestToString;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\Request\LatLngRequestInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\Request\LatLngRequestInterfaceFactory;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressRequestInterface;

/**
 * @inheritdoc
 */
class GetLatLngRequestFromAddress implements GetLatLngRequestFromAddressInterface
{
    private const GOOGLE_ENDPOINT = 'https://maps.google.com/maps/api/geocode/json';

    /**
     * @var array
     */
    private $latLngCache = [];

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LatLngRequestInterfaceFactory
     */
    private $latLngRequestInterfaceFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var GetApiKey
     */
    private $getApiKey;

    /**
     * @var AddressRequestToComponentsString
     */
    private $addressRequestToComponentsString;

    /**
     * @var AddressRequestToString
     */
    private $addressRequestToString;

    /**
     * @var AddressRequestToQueryString
     */
    private $addressRequestToQueryString;

    /**
     * GetLatLngRequestFromAddress constructor.
     *
     * @param ClientInterface $client
     * @param LatLngRequestInterfaceFactory $latLngRequestInterfaceFactory
     * @param Json $json
     * @param GetApiKey $getApiKey
     * @param AddressRequestToComponentsString $addressRequestToComponentsString
     * @param AddressRequestToQueryString $addressRequestToQueryString
     * @param AddressRequestToString $addressRequestToString
     */
    public function __construct(
        ClientInterface $client,
        LatLngRequestInterfaceFactory $latLngRequestInterfaceFactory,
        Json $json,
        GetApiKey $getApiKey,
        AddressRequestToComponentsString $addressRequestToComponentsString,
        AddressRequestToQueryString $addressRequestToQueryString,
        AddressRequestToString $addressRequestToString
    ) {
        $this->client = $client;
        $this->latLngRequestInterfaceFactory = $latLngRequestInterfaceFactory;
        $this->json = $json;
        $this->getApiKey = $getApiKey;
        $this->addressRequestToComponentsString = $addressRequestToComponentsString;
        $this->addressRequestToString = $addressRequestToString;
        $this->addressRequestToQueryString = $addressRequestToQueryString;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute(AddressRequestInterface $addressRequest): LatLngRequestInterface
    {
        $cacheKey = $addressString = $this->addressRequestToString->execute($addressRequest);

        if (!isset($this->latLngCache[$cacheKey])) {
            $queryString = http_build_query([
                'key' => $this->getApiKey->execute(),
                'components' => $this->addressRequestToComponentsString->execute($addressRequest),
                'address' => $this->addressRequestToQueryString->execute($addressRequest),
            ]);

            $this->client->get(self::GOOGLE_ENDPOINT . '?' . $queryString);
            if ($this->client->getStatus() !== 200) {
                throw new LocalizedException(__('Unable to connect google API for geocoding'));
            }

            $res = $this->json->unserialize($this->client->getBody());

            if ($res['status'] !== 'OK') {
                throw new LocalizedException(__('Unable to geocode address %1', $addressString));
            }

            $location = $res['results'][0]['geometry']['location'];
            $this->latLngCache[$cacheKey] = $this->latLngRequestInterfaceFactory->create([
                'lat' => (float)$location['lat'],
                'lng' => (float)$location['lng'],
            ]);
        }

        return $this->latLngCache[$cacheKey];
    }
}
