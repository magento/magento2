<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SearchStorefront\Model\Scope\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;

class Store implements \Magento\Framework\App\ScopeResolverInterface
{
    const STORE_TABLE = 'store';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\SearchStorefront\Model\Scope\ScopeFactory
     */
    private $scopeFactory;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\SearchStorefront\Model\Scope\ScopeFactory $scopeFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\SearchStorefront\Model\Scope\ScopeFactory $scopeFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeFactory = $scopeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope($scopeId = null)
    {
        $scopeData = $this->loadData($scopeId);
        return $this->populate($scopeData);
    }

    /**
     * Retrieve a list of available stores
     *
     * @return \Magento\Store\Model\Store[]
     */
    public function getScopes()
    {
        $scopes = [];
        $scopeData = $this->loadData(null, true);

        foreach ($scopeData as $item) {
            $scopes[] = $this->populate($item);
        }

        return $scopes;
    }

    /**
     * @param null $scopeId
     * @param bool $loadAll
     * @return array|mixed
     */
    public function loadData($scopeId = null, $loadAll = false)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['stores' => $connection->getTableName(self::STORE_TABLE)]);

        if ($loadAll) {
            return $connection->fetchAll($select);
        }

        if ($scopeId) {
            $select->where('store_id = ?', $scopeId);
        } else {
            $select->join(
                ['store_group' => $this->resourceConnection->getTableName(Group::GROUP_TABLE)],
                'stores.store_id = store_group.default_store_id',
                []
            )->join(
                ['websites' => $this->resourceConnection->getTableName(Website::WEBSITE_TABLE)],
                'websites.default_group_id = store_group.group_id',
                ['websites_code' => 'code']
            )->where('websites.is_default = 1');
        }

        return $connection->fetchRow($select);
    }

    /**
     * @param array $data
     */
    private function populate(array $data = [])
    {
        if (empty($data)) {
            throw new NoSuchEntityException(__('Cannot find requested store"'));
        }

        /** @var \Magento\Framework\App\ScopeInterface $object */
        $object = $this->scopeFactory->create();
        $object->setData('id', $data['store_id']);
        $object->setData('code', $data['code']);
        $object->setData('name', $data['name']);
        $object->setData('scope_type', 'store');

        return $object;
    }
}
