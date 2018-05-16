<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Model\OptionSource;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Provide option values for UI
 *
 * @api
 */
class RegionSource implements OptionSourceInterface
{
    /**
     * Region collection factory
     *
     * @var CollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * Source data
     *
     * @var null|array
     */
    private $sourceData;

    /**
     * @param CollectionFactory $regionCollectionFactory
     */
    public function __construct(CollectionFactory $regionCollectionFactory)
    {
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->sourceData) {
            $regionCollection = $this->regionCollectionFactory->create();
            $this->sourceData = $regionCollection->toOptionArray();
        }
        return $this->sourceData;
    }
}
