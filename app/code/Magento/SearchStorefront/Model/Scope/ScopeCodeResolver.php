<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SearchStorefront\Model\Scope;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for resolving scope code
 * @TODO it is temporary solution - sql queries need to be removed in future
 */
class ScopeCodeResolver
{
    /**
     * @var array
     */
    private $scopeTableMapper = [
        'stores' => ['table' => 'store', 'field' => 'store_id']
    ];

    /**
     * @var array
     */
    private $resolvedScopeCodes = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Resolve scope code
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function resolve($scopeType, $scopeCode) : string
    {
        if (isset($this->resolvedScopeCodes[$scopeType][$scopeCode])) {
            return $this->resolvedScopeCodes[$scopeType][$scopeCode];
        }

        if ($scopeType !== \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $resolverScopeCode = $this->get($scopeType, $scopeCode);
        } else {
            $resolverScopeCode = $scopeCode;
        }

        if ($resolverScopeCode instanceof ScopeInterface) {
            $resolverScopeCode = $resolverScopeCode->getCode();
        }

        if ($scopeCode === null) {
            $scopeCode = $resolverScopeCode;
        }

        $this->resolvedScopeCodes[$scopeType][$scopeCode] = $resolverScopeCode;

        return $resolverScopeCode;
    }

    /**
     * Clean resolvedScopeCodes, store codes may have been renamed
     *
     * @return void
     */
    public function clean() : void
    {
        $this->resolvedScopeCodes = [];
    }

    public function get($scopeType, $scopeCode) : string
    {
        if (empty($this->scopeTableMapper[$scopeType])) {
            throw new LocalizedException(__('Unsupported scope type "%1"', $scopeType));
        }

        $connection = $this->resourceConnection->getConnection();
        $field = $this->scopeTableMapper[$scopeType]['field'];
        $select = $connection->select();

        if ($scopeCode) {
            $select->from(
                ['stores' => $this->resourceConnection->getTableName('store')],
                ['stores_code' => 'code']
            )->join(
                ['websites' => $this->resourceConnection->getTableName('store_website')],
                'websites.website_id = stores.website_id',
                ['websites_code' => 'code']
            )->where("{$scopeCode}.{$field} = ?", $scopeCode);
        } else {
            $select->from(
                ['stores' => $this->resourceConnection->getTableName('store')],
                ['stores_code' => 'code']
            )->join(
                ['store_group' => $this->resourceConnection->getTableName('store_group')],
                'stores.store_id = store_group.default_store_id',
                []
            )->join(
                ['websites' => $this->resourceConnection->getTableName('store_website')],
                'websites.default_group_id = store_group.group_id',
                ['websites_code' => 'code']
            )->where('websites.is_default = 1');
        }

        return $result[$scopeType . '_code'] ?? '';
    }
}
