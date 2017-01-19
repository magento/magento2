<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Framework\App\ProductMetadataInterface;

/**
 * Provides platform name, edition and version info
 */
class ClientVersionBuilder
{
    /**
     * @var string
     */
    private static $clientVersion = '1.0';

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

    /**
     * Returns version info
     *
     * @return array
     */
    public function build()
    {
        return [
            'platformAndClient' => [
                'storePlatform' => $this->productMetadata->getName() . ' ' . $this->productMetadata->getEdition(),
                'storePlatformVersion' => $this->productMetadata->getVersion(),
                'signifydClientApp' => $this->productMetadata->getName(),
                'signifydClientAppVersion' => self::$clientVersion,
            ]
        ];
    }
}
