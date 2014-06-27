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
namespace Magento\Catalog\Service\V1\Product\Attribute;

use Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory;
use Magento\Catalog\Service\V1\Data\Eav\Attribute;
use Magento\Catalog\Service\V1\Data\Eav\AttributeBuilder;
use Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\TypeBuilder;
use Magento\Catalog\Service\V1\Data\Product\Attribute\SearchResultsBuilder;
use Magento\Catalog\Service\V1\ProductMetadataServiceInterface;
use Magento\Eav\Model\Resource\Entity\Attribute\Collection;
use Magento\Framework\Service\V1\Data\Search\FilterGroup;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Class ReadService
 *
 * @package Magento\Catalog\Service\V1\Product\Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadService implements ReadServiceInterface
{
    /**
     * @var ProductMetadataServiceInterface
     */
    private $metadataService;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory
     */
    private $inputTypeFactory;

    /**
     * @var TypeBuilder
     */
    private $attributeTypeBuilder;

    /**
     * @var SearchResultsBuilder
     */
    private $searchResultsBuilder;

    /**
     * @var Collection
     */
    private $attributeCollection;

    /**
     * @var AttributeBuilder
     */
    protected $attributeBuilder;

    /**
     * @param ProductMetadataServiceInterface $metadataService
     * @param InputtypeFactory $inputTypeFactory
     * @param SearchResultsBuilder $searchResultsBuilder
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Collection $attributeCollection
     * @param AttributeBuilder $attributeBuilder
     * @param TypeBuilder $attributeTypeBuilder
     */
    public function __construct(
        ProductMetadataServiceInterface $metadataService,
        InputtypeFactory $inputTypeFactory,
        SearchResultsBuilder $searchResultsBuilder,
        \Magento\Eav\Model\Resource\Entity\Attribute\Collection $attributeCollection,
        AttributeBuilder $attributeBuilder,
        TypeBuilder $attributeTypeBuilder
    ) {
        $this->metadataService = $metadataService;
        $this->inputTypeFactory = $inputTypeFactory;
        $this->attributeTypeBuilder = $attributeTypeBuilder;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->attributeCollection = $attributeCollection;
        $this->attributeBuilder = $attributeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function types()
    {
        $types = [];
        $inputType = $this->inputTypeFactory->create();

        foreach ($inputType->toOptionArray() as $option) {
            $types[] = $this->attributeTypeBuilder->populateWithArray($option)->create();
        }
        return $types;
    }

    /**
     * {@inheritdoc}
     */
    public function info($id)
    {
        return $this->metadataService->getAttributeMetadata(
            ProductMetadataServiceInterface::ENTITY_TYPE_PRODUCT,
            $id
        );
    }

    /**
     * {@inheritdoc}
     */
    public function search(\Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria)
    {
        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $this->attributeCollection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $field => $direction) {
                $field = $this->translateField($field);
                $this->attributeCollection->addOrder($field, $direction == SearchCriteria::SORT_ASC ? 'ASC' : 'DESC');
            }
        }
        $this->attributeCollection->join(
            array('additional_table' => $this->attributeCollection->getTable('catalog_eav_attribute')),
            'main_table.attribute_id = additional_table.attribute_id',
            [
                'frontend_input_renderer',
                'is_global',
                'is_visible',
                'is_searchable',
                'is_filterable',
                'is_comparable',
                'is_visible_on_front',
                'is_html_allowed_on_front',
                'is_used_for_price_rules',
                'is_filterable_in_search',
                'used_in_product_listing',
                'used_for_sort_by',
                'apply_to',
                'is_visible_in_advanced_search',
                'position',
                'is_wysiwyg_enabled',
                'is_used_for_promo_rules',
                'is_configurable',
                'search_weight',
            ]
        );

        $this->attributeCollection->setCurPage($searchCriteria->getCurrentPage());
        $this->attributeCollection->setPageSize($searchCriteria->getPageSize());
        $this->searchResultsBuilder->setTotalCount($this->attributeCollection->getSize());

        $attributes = array();
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        foreach ($this->attributeCollection as $attribute) {
            $attributes[] = $this->attributeBuilder->setId($attribute->getAttributeId())
                ->setCode($attribute->getAttributeCode())
                ->setFrontendLabel($attribute->getData('frontend_label'))
                ->setDefaultValue($attribute->getDefaultValue())
                ->setIsRequired((boolean)$attribute->getData('is_required'))
                ->setIsUserDefined((boolean)$attribute->getData('is_user_defined'))
                ->setFrontendInput($attribute->getData('frontend_input'))
                ->create();
        }

        $this->searchResultsBuilder->setItems($attributes);
        return $this->searchResultsBuilder->create();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $this->translateField($filter->getField());
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Translates a field name to a DB column name for use in collection queries.
     *
     * @param string $field a field name that should be translated to a DB column name.
     * @return string
     */
    protected function translateField($field)
    {
        switch ($field) {
            case Attribute::ID:
                return 'attribute_id';
            case Attribute::CODE:
                return 'attribute_code';
            default:
                return $field;
        }
    }
}
