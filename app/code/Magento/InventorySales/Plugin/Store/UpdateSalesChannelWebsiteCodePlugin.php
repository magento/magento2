<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Store;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySales\Model\AssignWebsiteToDefaultStock;
use Magento\InventorySales\Model\ResourceModel\UpdateSalesChannelWebsiteCode;

class UpdateSalesChannelWebsiteCodePlugin
{
    /**
     * @var UpdateSalesChannelWebsiteCode
     */
    private $updateSalesChannelWebsiteCode;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AssignWebsiteToDefaultStock
     */
    private $assignWebsiteToDefaultStock;

    /**
     * WebsiteResourcePlugin constructor.
     * @param UpdateSalesChannelWebsiteCode $updateSalesChannelWebsiteCode
     * @param AssignWebsiteToDefaultStock $assignWebsiteToDefaultStock
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        UpdateSalesChannelWebsiteCode $updateSalesChannelWebsiteCode,
        AssignWebsiteToDefaultStock $assignWebsiteToDefaultStock,
        ResourceConnection $resourceConnection
    ) {
        $this->updateSalesChannelWebsiteCode = $updateSalesChannelWebsiteCode;
        $this->resourceConnection = $resourceConnection;
        $this->assignWebsiteToDefaultStock = $assignWebsiteToDefaultStock;
    }

    /**
     * Get code from database
     * @param int $websiteId
     * @return string
     */
    private function getCodeFromDatabase(int $websiteId): ?string
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('store_website');
        $selectQry = $connection->select()->from($tableName, 'code')->where('website_id = ?', $websiteId);
        return (string) $connection->fetchOne($selectQry);
    }

    /**
     * @param \Magento\Store\Model\ResourceModel\Website $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Store\Model\ResourceModel\Website
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Validation\ValidationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Store\Model\ResourceModel\Website $subject,
        callable $proceed,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $newCode = $object->getCode();
        $oldCode = '';

        if ($object->getId()) {
            $oldCode = $this->getCodeFromDatabase((int) $object->getId());
        }

        $res = $proceed($object);

        if ($oldCode && ($oldCode !== $newCode)) {
            $this->updateSalesChannelWebsiteCode->execute($oldCode, $newCode);
        }

        $this->assignWebsiteToDefaultStock->execute($object);

        return $res;
    }
}
