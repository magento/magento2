<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Attribute;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogEavAttribute;
use Magento\Store\Model\Website;
use Magento\Framework\Model\Entity\ScopeInterface;

/**
 * Builds scope-related conditions for catalog attributes
 *
 * Class ConditionBuilder
 * @package Magento\Catalog\Model\ResourceModel\Attribute
 * @since 2.2.0
 */
class ConditionBuilder
{
    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * ConditionBuilder constructor
     * @param StoreManagerInterface $storeManager
     * @since 2.2.0
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Returns conditions for existing attribute actions (update/delete) if attribute scope is "website"
     *
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param ScopeInterface[] $scopes
     * @param string $linkFieldValue
     * @return array
     * @since 2.2.0
     */
    public function buildExistingAttributeWebsiteScope(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        $website = $this->getWebsiteForWebsiteScope($scopes);
        if (!$website) {
            return [];
        }
        $storeIds = $website->getStoreIds();

        $condition = [
            $metadata->getLinkField() . ' = ?' => $linkFieldValue,
            'attribute_id = ?' => $attribute->getAttributeId(),
        ];

        $conditions = [];
        foreach ($storeIds as $storeId) {
            $identifier = $metadata->getEntityConnection()->quoteIdentifier(Store::STORE_ID);
            $condition[$identifier . ' = ?'] = $storeId;
            $conditions[] = $condition;
        }

        return $conditions;
    }

    /**
     * Returns conditions for new attribute action (insert) if attribute scope is "website"
     *
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param ScopeInterface[] $scopes
     * @param string $linkFieldValue
     * @return array
     * @since 2.2.0
     */
    public function buildNewAttributesWebsiteScope(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        $website = $this->getWebsiteForWebsiteScope($scopes);
        if (!$website) {
            return [];
        }
        $storeIds = $website->getStoreIds();

        $condition = [
            $metadata->getLinkField() => $linkFieldValue,
            'attribute_id' => $attribute->getAttributeId(),
        ];

        $conditions = [];
        foreach ($storeIds as $storeId) {
            $condition[Store::STORE_ID] = $storeId;
            $conditions[] = $condition;
        }

        return $conditions;
    }

    /**
     * @param array $scopes
     * @return null|Website
     * @since 2.2.0
     */
    private function getWebsiteForWebsiteScope(array $scopes)
    {
        $store = $this->getStoreFromScopes($scopes);
        return $store ? $store->getWebsite() : null;
    }

    /**
     * @param ScopeInterface[] $scopes
     * @return StoreInterface|null
     * @since 2.2.0
     */
    private function getStoreFromScopes(array $scopes)
    {
        foreach ($scopes as $scope) {
            if (Store::STORE_ID === $scope->getIdentifier()) {
                return $this->storeManager->getStore($scope->getValue());
            }
        }

        return null;
    }
}
