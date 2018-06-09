<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Store;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

class WebsiteResourcePlugin
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * WebsiteResourcePlugin constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get code from database
     * @param \Magento\Store\Model\ResourceModel\Website $resourceModel
     * @param int $websiteId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCodeFromDatabase(
        \Magento\Store\Model\ResourceModel\Website $resourceModel,
        int $websiteId
    ) {
        $connection = $resourceModel->getConnection();
        $qry = $connection->select()->from($resourceModel->getMainTable(), 'code')->where('website_id = ?', $websiteId);
        return (string) $connection->fetchOne($qry);
    }

    /**
     * @param string $oldCode
     * @param string $newCode
     */
    private function updateSalesChannel(
        string $oldCode,
        string $newCode
    ) {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('inventory_stock_sales_channel');
        $connection->update($tableName, [
            SalesChannelInterface::CODE => $newCode,
        ], $connection->quoteInto(SalesChannelInterface::CODE . "=? and type='website'", $oldCode));
    }

    /**
     * @param \Magento\Store\Model\ResourceModel\Website $subject
     * @param callable $proceed
     * @param \Magento\Store\Model\Website $object
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundSave(
        \Magento\Store\Model\ResourceModel\Website $subject,
        callable $proceed,
        \Magento\Store\Model\Website $object
    ) {
        if ($object->getId()) {
            // Keep database consistency while updating the website code
            // See https://github.com/magento-engcom/msi/issues/1306
            $oldCode = $this->getCodeFromDatabase($subject, (int) $object->getId());
            if ($oldCode !== $object->getCode()) {
                $this->updateSalesChannel($oldCode, $object->getCode());
            }
        }

        $res = $proceed($object);
        return $res;
    }
}