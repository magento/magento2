<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Class AddWebsitesFieldToCollection
 *
 * @api
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
