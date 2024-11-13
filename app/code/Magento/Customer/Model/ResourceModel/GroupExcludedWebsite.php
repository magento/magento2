<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Customer\Model\Cache\GroupExcludedWebsiteCache;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Excluded customer group website resource model.
 */
class GroupExcludedWebsite extends AbstractDb implements ResetAfterRequestInterface
{
    /**
     * @var GroupExcludedWebsiteCache $groupExcludedWebsiteCache
     */
    private GroupExcludedWebsiteCache $groupExcludedWebsiteCache;

    /**
     * @param Context $context
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param GroupExcludedWebsiteCache $groupExcludedWebsiteCache
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        GroupExcludedWebsiteCache $groupExcludedWebsiteCache,
        $connectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
        $this->groupExcludedWebsiteCache = $groupExcludedWebsiteCache;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_group_excluded_website', 'entity_id');
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->groupExcludedWebsiteCache->invalidate();
    }

    /**
     * Makes sure ExcludedWebsiteCache is invalidated when excluded websites are modified
     */
    public function invalidateCache()
    {
        $this->_resetState();
    }

    /**
     * Retrieve excluded website ids related to customer group.
     *
     * @param int $customerGroupId
     * @return array
     * @throws LocalizedException
     */

    public function loadCustomerGroupExcludedWebsites(int $customerGroupId): array
    {
        if ($this->groupExcludedWebsiteCache->isCached($customerGroupId)) {
            return $this->groupExcludedWebsiteCache->getFromCache($customerGroupId);
        }
        
        $connection = $this->getConnection();
        $bind = ['customer_group_id' => $customerGroupId];

        $select = $connection->select()->from(
            $this->getMainTable(),
            ['website_id']
        )->where(
            'customer_group_id = :customer_group_id'
        );

        $this->groupExcludedWebsiteCache->addToCache($customerGroupId, $connection->fetchCol($select, $bind));
        return $this->groupExcludedWebsiteCache->getFromCache($customerGroupId);
    }

    /**
     * Retrieve all excluded website ids per related customer group.
     *
     * @return array
     * @throws LocalizedException
     */
    public function loadAllExcludedWebsites(): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getMainTable(),
            ['customer_group_id', 'website_id']
        );

        return $connection->fetchAll($select);
    }

    /**
     * Delete customer group with its excluded websites.
     *
     * @param int $customerGroupId
     * @return GroupExcludedWebsite
     * @throws LocalizedException
     */
    public function delete($customerGroupId)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        $this->invalidateCache();
        try {
            $where = $connection->quoteInto('customer_group_id = ?', $customerGroupId);
            $connection->delete(
                $this->getMainTable(),
                $where
            );
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Delete customer group excluded website by id.
     *
     * @param int $websiteId
     * @return int
     * @throws LocalizedException
     */
    public function deleteByWebsite(int $websiteId): int
    {
        return $this->getConnection()->delete($this->getMainTable(), ['website_id = ?' => $websiteId]);
    }
}
