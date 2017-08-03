<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Framework\App\ProductMetadataInterface;

/**
 * Provides platform name, edition and version info
 * @since 2.2.0
 */
class ClientVersionBuilder
{
    /**
     * @var string
     * @since 2.2.0
     */
    private static $clientVersion = '1.0';

    /**
     * @var ProductMetadataInterface
     * @since 2.2.0
     */
    private $productMetadata;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @since 2.2.0
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
     * @since 2.2.0
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
