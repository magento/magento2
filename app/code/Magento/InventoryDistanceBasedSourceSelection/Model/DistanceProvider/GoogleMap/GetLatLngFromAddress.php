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
use Magento\InventoryDistanceBasedSourceSelection\Model\Convert\AddressToComponentsString;
use Magento\InventoryDistanceBasedSourceSelection\Model\Convert\AddressToQueryString;
use Magento\InventoryDistanceBasedSourceSelection\Model\Convert\AddressToString;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterfaceFactory;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetLatLngFromAddressInterface;

/**
 * @inheritdoc
 */
class GetLatLngFromAddress implements GetLatLngFromAddressInterface
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
     * @var LatLngInterface
     */
    private $latLngInterfaceFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var GetApiKey
     */
    private $getApiKey;

    /**
     * @var AddressToComponentsString
     */
    private $addressToComponentsString;

    /**
     * @var AddressToString
     */
    private $addressToString;

    /**
     * @var AddressToQueryString
     */
    private $addressToQueryString;

    /**
     * GetLatLngFromAddress constructor.
     *
     * @param ClientInterface $client
     * @param LatLngInterfaceFactory $latLngInterfaceFactory
     * @param Json $json
     * @param GetApiKey $getApiKey
     * @param AddressToComponentsString $addressToComponentsString
     * @param AddressToQueryString $addressToQueryString
     * @param AddressToString $addressToString
     */
    public function __construct(
        ClientInterface $client,
        LatLngInterfaceFactory $latLngInterfaceFactory,
        Json $json,
        GetApiKey $getApiKey,
        AddressToComponentsString $addressToComponentsString,
        AddressToQueryString $addressToQueryString,
        AddressToString $addressToString
    ) {
        $this->client = $client;
        $this->latLngInterfaceFactory = $latLngInterfaceFactory;
        $this->json = $json;
        $this->getApiKey = $getApiKey;
        $this->addressToComponentsString = $addressToComponentsString;
        $this->addressToString = $addressToString;
        $this->addressToQueryString = $addressToQueryString;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute(AddressInterface $address): LatLngInterface
    {
        $cacheKey = $addressString = $this->addressToString->execute($address);

        if (!isset($this->latLngCache[$cacheKey])) {
            $queryString = http_build_query([
                'key' => $this->getApiKey->execute(),
                'components' => $this->addressToComponentsString->execute($address),
                'address' => $this->addressToQueryString->execute($address),
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
            $this->latLngCache[$cacheKey] = $this->latLngInterfaceFactory->create([
                'lat' => (float)$location['lat'],
                'lng' => (float)$location['lng'],
            ]);
        }

        return $this->latLngCache[$cacheKey];
    }
}
