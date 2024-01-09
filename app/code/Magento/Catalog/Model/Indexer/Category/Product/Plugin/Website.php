<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;

class Website
{
    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var \Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface
     */
    private $pillPut;

    /**
     * @param TableMaintainer $tableMaintainer
     * @param \Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface|null $pillPut
     */
    public function __construct(
        TableMaintainer $tableMaintainer,
        \Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface $pillPut = null
    ) {
        $this->tableMaintainer = $tableMaintainer;
        $this->pillPut = $pillPut ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface::class);
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
     * @throws \Exception
     */
    public function afterDelete(AbstractDb $subject, AbstractDb $objectResource, AbstractModel $website)
    {
        foreach ($website->getStoreIds() as $storeId) {
            $this->tableMaintainer->dropTablesForStore((int)$storeId);
        }
        $this->pillPut->put();
        return $objectResource;
    }
}
