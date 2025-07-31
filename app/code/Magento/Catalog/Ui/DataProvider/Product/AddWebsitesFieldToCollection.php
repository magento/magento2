<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Class AddWebsitesFieldToCollection
 *
 * @api
 * @since 100.0.2
 */
class AddWebsitesFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection->addWebsiteNamesToResult();
    }
}
