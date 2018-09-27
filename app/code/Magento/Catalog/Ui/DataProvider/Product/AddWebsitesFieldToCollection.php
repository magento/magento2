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
 * @since 100.0.2
 */
class AddWebsitesFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection */

        //Field added to select properly, but order uses entire expression instead of alias
        $collection->joinField(
            'websites',
            'catalog_product_website',
            new \Zend_Db_Expr("GROUP_CONCAT(at_websites.website_id SEPARATOR ',')"),
            'product_id=entity_id',
             null,
            'left'
        );
        $collection->getSelect()->group('entity_id');

        //Field added to select, but only takes one "website_id" value, we need group_concat to have this work properly
//        $collection->joinField(
//            'websites',
//            'catalog_product_website',
//            'website_id',
//            'product_id=entity_id',
//             null,
//            'left'
//        );
//        $collection->getSelect()->group('entity_id');

        //Field added to select properly, but filter isn't working
        //app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php:L414 doesn't contain "websites" field, as we aren't using "joinField" method on collection
//        $websiteExpr = "GROUP_CONCAT(websites_table.website_id SEPARATOR ',')";
//        $collection->getSelect()->joinLeft(
//            ['websites_table' => $collection->getTable('catalog_product_website')],
//            'websites_table.product_id=entity_id',
//            [$field => new \Zend_Db_Expr($websiteExpr)]
//        );
//        $collection->getSelect()->group('entity_id');

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection->addWebsiteNamesToResult();
    }
}
