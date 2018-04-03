<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Indexer\Category\Product\TableResolver;

class Website
{
    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @param TableResolver $tableResolver
     */
    public function __construct(
        TableResolver $tableResolver = null
    ) {
        $this->tableResolver = $tableResolver ?: ObjectManager::getInstance()->get(TableResolver::class);
    }

    /**
     * Delete catalog_category_product indexer tables for deleted website
     *
     * @param AbstractDb $subject
     * @param AbstractDb $objectResource
     * @param AbstractModel $website
     *
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(AbstractDb $subject, AbstractDb $objectResource, AbstractModel $website)
    {
        foreach ($website->getStoreIds() as $storeId) {
            $this->tableResolver->dropTablesForStore($storeId);
        }
        return $objectResource;
    }
}
