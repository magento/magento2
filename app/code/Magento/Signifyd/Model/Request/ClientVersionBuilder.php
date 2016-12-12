<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Request;

use Magento\Framework\App\ProductMetadata;

/**
 * Class ClientVersionBuilder
 */
class ClientVersionBuilder
{
    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    /**
     * @param ProductMetadata $productMetadata
     */
    public function __construct(
        ProductMetadata $productMetadata
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
