<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Resource product indexer price factory
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\Indexer\DimensionalIndexerInterface;

class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create indexer price
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $data = [])
    {
        $indexerPrice = $this->_objectManager->create($className, $data);

        if ($indexerPrice instanceof PriceInterface || $indexerPrice instanceof DimensionalIndexerInterface) {
            return $indexerPrice;
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __(
                'Price indexer "%1" must implement %2 or %3',
                $className,
                PriceInterface::class,
                DimensionalIndexerInterface::class
            )
        );
    }
}
