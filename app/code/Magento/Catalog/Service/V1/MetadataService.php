<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Resource\Entity\Attribute\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata;
use Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel;
use Magento\Framework\Service\V1\Data\Search\FilterGroup;
use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Framework\Service\V1\Data\SortOrder;

/**
 * Class MetadataService
 *
 * @package Magento\Catalog\Service\V1
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetadataService implements MetadataServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Data\Eav\AttributeMetadataBuilder
     */
    private $attributeMetadataBuilder;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var Data\Product\SearchResultsBuilder
     */
    private $searchResultsBuilder;

    /**
     * Attribute DTO Field Mapping
     * @var array
     */
    private static $mapping = [
        'id' => 'attribute_id',
        'code' => 'attribute_code',
        'required' => 'is_required',
        'user_defined' => 'is_user_defined',
        'unique' => 'is_unique',
        'global' => 'is_global',
        'visible' => 'is_visible',
        'searchable' => 'is_searchable',
        'filterable' => 'is_filterable',
        'comparable' => 'is_comparable',
        'visible_on_front' => 'is_visible_on_front',
        'html_allowed_on_front' => 'is_html_allowed_on_front',
        'used_for_price_rules' => 'is_used_for_price_rules',
        'filterable_in_search' => 'is_filterable_in_search',
        'visible_in_advanced_search' => 'is_visible_in_advanced_search',
        'wysiwyg_enabled' => 'is_wysiwyg_enabled',
        'used_for_promo_rules' => 'is_used_for_promo_rules',
        'configurable' => 'is_configurable',
    ];

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param Data\Eav\AttributeMetadataBuilder $attributeMetadataBuilder
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory $attributeCollectionFactory
     * @param Data\Product\SearchResultsBuilder $searchResultsBuilder
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder $attributeMetadataBuilder,
        \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
        Data\Product\SearchResultsBuilder $searchResultsBuilder
    ) {
        $this->eavConfig = $eavConfig;
        $this->scopeResolver = $scopeResolver;
        $this->attributeMetadataBuilder = $attributeMetadataBuilder;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->searchResultsBuilder = $searchResultsBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeMetadata($entityType, $attributeCode)
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);
        if ($attribute->getId()) {
            $attributeMetadata = $this->createMetadataAttribute($attribute);
            return $attributeMetadata;
        } else {
            throw (new NoSuchEntityException('entityType', array($entityType)))
                ->singleField('attributeCode', $attributeCode);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributeMetadata(
        $entityType,
        SearchCriteria $searchCriteria
    ) {
        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);
        /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Collection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->join(
            array('entity_type' => $attributeCollection->getTable('eav_entity_type')),
            'main_table.entity_type_id = entity_type.entity_type_id',
            []
        );
        $attributeCollection->addFieldToFilter('entity_type_code', ['eq' => $entityType]);
        $attributeCollection->join(
            ['eav_entity_attribute' => $attributeCollection->getTable('eav_entity_attribute')],
            'main_table.attribute_id = eav_entity_attribute.attribute_id',
            []
        );
        $attributeCollection->join(
            array('additional_table' => $attributeCollection->getTable('catalog_eav_attribute')),
            'main_table.attribute_id = additional_table.attribute_id',
            []
        );
        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $attributeCollection);
        }
        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $attributeCollection->addOrder(
                $this->translateField($sortOrder->getField()),
                ($sortOrder->getDirection() == SearchCriteria::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }

        $totalCount = $attributeCollection->getSize();

        // Group attributes by id to prevent duplicates with different attribute sets
        $attributeCollection->addAttributeGrouping();

        $attributeCollection->setCurPage($searchCriteria->getCurrentPage());
        $attributeCollection->setPageSize($searchCriteria->getPageSize());

        $attributes = [];
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        foreach ($attributeCollection as $attribute) {
            $attributes[] = $this->getAttributeMetadata($entityType, $attribute->getAttributeCode());
        }
        $this->searchResultsBuilder->setItems($attributes);
        $this->searchResultsBuilder->setTotalCount($totalCount);
        return $this->searchResultsBuilder->create();
    }

    /**
     * @param  AbstractAttribute $attribute
     * @return Data\Eav\AttributeMetadata
     */
    private function createMetadataAttribute($attribute)
    {
        $data = $this->booleanPrefixMapper($attribute->getData());

        // fill options and validate rules
        $data[AttributeMetadata::OPTIONS] = $attribute->usesSource()
            ? $attribute->getSource()->getAllOptions() : array();
        $data[AttributeMetadata::VALIDATION_RULES] = $attribute->getValidateRules();

        // fill scope
        $data[AttributeMetadata::SCOPE] = $attribute->isScopeGlobal()
            ? 'global' : ($attribute->isScopeWebsite() ? 'website' : 'store');

        $data[AttributeMetadata::FRONTEND_LABEL] = [];
        $data[AttributeMetadata::FRONTEND_LABEL][0] = array(
            FrontendLabel::STORE_ID => 0,
            FrontendLabel::LABEL => $attribute->getFrontendLabel()
        );
        if (is_array($attribute->getStoreLabels())) {
            foreach ($attribute->getStoreLabels() as $storeId => $label) {
                $data[AttributeMetadata::FRONTEND_LABEL][$storeId] = array(
                    FrontendLabel::STORE_ID => $storeId,
                    FrontendLabel::LABEL => $label
                );
            }
        }
        return $this->attributeMetadataBuilder->populateWithArray($data)->create();
    }

    /**
     * Remove 'is_' prefixes for Attribute fields to make DTO interface more natural
     *
     * @param array $attributeFields
     * @return array
     */
    private function booleanPrefixMapper(array $attributeFields)
    {
        $prefix = 'is_';
        foreach ($attributeFields as $key => $value) {
            if (strpos($key, $prefix) !== 0) {
                continue;
            }
            $postfix = substr($key, strlen($prefix));
            if (!isset($attributeFields[$postfix])) {
                $attributeFields[$postfix] = $value;
                unset($attributeFields[$key]);
            }
        }
        return $attributeFields;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Service\V1\Data\Search\FilterGroup  $filterGroup
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter(
                $this->translateField($filter->getField()),
                [$condition => $filter->getValue()]
            );
        }
    }

    /**
     * Translates a field name to a DB column name for use in collection queries.
     *
     * @param string $field a field name that should be translated to a DB column name.
     * @return string
     */
    private function translateField($field)
    {
        if (isset(self::$mapping[$field])) {
            return self::$mapping[$field];
        } else {
            return $field;
        }

    }
}
