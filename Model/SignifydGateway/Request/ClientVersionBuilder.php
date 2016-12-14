<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class ClientVersionBuilder
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
