<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            'clientVersion' => [
                'platform' => $this->productMetadata->getName() . ' ' . $this->productMetadata->getEdition(),
                'platformVersion' => $this->productMetadata->getVersion()
            ]
        ];
    }
}
