<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\Translit;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Mapper\FormElement as FormElementMapper;
use Magento\Ui\DataProvider\Mapper\MetaProperties as MetaPropertiesMapper;
use Magento\Ui\Component\Form\Element\Wysiwyg as WysiwygElement;

/**
 * Class Eav
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Eav extends AbstractModifier
{
    const SORT_ORDER_MULTIPLIER = 10;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var array
     */
    protected $prevSetAttributes;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FormElementMapper
     */
    protected $formElementMapper;

    /**
     * @var MetaPropertiesMapper
     */
    protected $metaPropertiesMapper;

    /**
     * @var EavAttribute[]
     */
    private $attributes = [];

    /**
     * @var AttributeGroupInterface[]
     */
    private $groups = [];

    /**
     * @var array
     */
    private $usedDefault = [];

    /**
     * @var array
     */
    private $canDisplayUseDefault = [];

    /**
     * @var ProductAttributeGroupRepositoryInterface
     */
    protected $attributeGroupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var EavAttributeFactory
     */
    private $eavAttributeFactory;

    /**
     * @var Translit
     */
    private $translitFilter;

    /**
     * @var array
     */
    private $bannedInputTypes = ['media_image'];

    /**
     * Initialize dependencies
     *
     * @param LocatorInterface $locator
     * @param EavValidationRules $eavValidationRules
     * @param Config $eavConfig
     * @param RequestInterface $request
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param FormElementMapper $formElementMapper
     * @param MetaPropertiesMapper $metaPropertiesMapper
     * @param ProductAttributeGroupRepositoryInterface $attributeGroupRepository
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param EavAttributeFactory $eavAttributeFactory
     * @param Translit $translitFilter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        EavValidationRules $eavValidationRules,
        Config $eavConfig,
        RequestInterface $request,
        GroupCollectionFactory $groupCollectionFactory,
        StoreManagerInterface $storeManager,
        FormElementMapper $formElementMapper,
        MetaPropertiesMapper $metaPropertiesMapper,
        ProductAttributeGroupRepositoryInterface $attributeGroupRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        EavAttributeFactory $eavAttributeFactory,
        Translit $translitFilter
    ) {
        $this->locator = $locator;
        $this->eavValidationRules = $eavValidationRules;
        $this->eavConfig = $eavConfig;
        $this->request = $request;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->formElementMapper = $formElementMapper;
        $this->metaPropertiesMapper = $metaPropertiesMapper;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->translitFilter = $translitFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $sortOrder = 0;
        foreach ($this->getGroups() as $groupCode => $group) {
            $attributes = !empty($this->getAttributes()[$groupCode]) ? $this->getAttributes()[$groupCode] : [];
            if ($attributes) {
                $meta[$groupCode]['children'] = $this->getAttributesMeta($attributes, $groupCode);
                $meta[$groupCode]['arguments']['data']['config']['componentType'] = Fieldset::NAME;
                $meta[$groupCode]['arguments']['data']['config']['label'] = __($group->getAttributeGroupName());
                $meta[$groupCode]['arguments']['data']['config']['collapsible'] = true;
                $meta[$groupCode]['arguments']['data']['config']['dataScope'] = self::DATA_SCOPE_PRODUCT;
                $meta[$groupCode]['arguments']['data']['config']['sortOrder'] =
                    $sortOrder * self::SORT_ORDER_MULTIPLIER;
            }
            $sortOrder++;
        }

        return $meta;
    }

    /**
     * Get attributes meta
     *
     * @param ProductAttributeInterface[] $attributes
     * @param string $groupCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta(array $attributes, $groupCode)
    {
        $meta = [];

        foreach ($attributes as $sortKey => $attribute) {
            if (in_array($attribute->getFrontendInput(), $this->bannedInputTypes)) {
                continue;
            }

            $code = $attribute->getAttributeCode();
            $child = $this->setupMetaProperties($attribute);

            $meta[static::CONTAINER_PREFIX . $code] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'breakLine' => false,
                            'label' => __($attribute->getDefaultFrontendLabel()),
                            'sortOrder' => $sortKey * self::SORT_ORDER_MULTIPLIER,
                            'required' => $attribute->getIsRequired(),
                        ],
                    ],
                ],
            ];

            if ($attribute->getIsWysiwygEnabled()) {
                $meta[static::CONTAINER_PREFIX . $code]['arguments']['data']['config']['component'] =
                    'Magento_Ui/js/form/components/group';
            }

            $child['arguments']['data']['config']['code'] = $code;
            $child['arguments']['data']['config']['source'] = $groupCode;
            $child['arguments']['data']['config']['scopeLabel'] = $this->getScopeLabel($attribute);
            $child['arguments']['data']['config']['globalScope'] = $this->isScopeGlobal($attribute);
            $child['arguments']['data']['config']['sortOrder'] = $sortKey * self::SORT_ORDER_MULTIPLIER;

            $canDisplayService = $this->canDisplayUseDefault($attribute);
            if ($canDisplayService) {
                $usedDefault = $this->usedDefault($attribute);
                $child['arguments']['data']['config']['service'] = [
                    'template' => 'ui/form/element/helper/service',
                ];
                $child['arguments']['data']['config']['disabled'] = $usedDefault;
            }

            if (!isset($child['arguments']['data']['config']['componentType'])) {
                $child['arguments']['data']['config']['componentType'] = Field::NAME;
            }

            // TODO: getAttributeModel() should not be used when MAGETWO-48284 is complete
            if (($rules = $this->eavValidationRules->build($this->getAttributeModel($attribute), $child))) {
                $child['arguments']['data']['config']['validation'] = $rules;
            }

            $meta[static::CONTAINER_PREFIX . $code]['children'][$code] = $child;
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var string $groupCode */
        foreach (array_keys($this->getGroups()) as $groupCode) {
            $attributes = !empty($this->getAttributes()[$groupCode]) ? $this->getAttributes()[$groupCode] : [];

            /* @var EavAttribute $attribute */
            foreach ($attributes as $attribute) {
                $data = $this->setAttributeValueToData($data, $attribute->getAttributeCode());
            }
        }

        return $data;
    }

    /**
     * Get product type
     *
     * @return null|string
     */
    protected function getTypeProduct()
    {
        return (string)$this->request->getParam('type', null) ?: $this->locator->getProduct()->getTypeId();
    }

    /**
     * Return prev set id
     *
     * @return int
     */
    protected function getPrevSetId()
    {
        return (int)$this->request->getParam('prev_set_id', 0);
    }

    /**
     * Retrieve groups
     *
     * @return AttributeGroupInterface[]
     */
    protected function getGroups()
    {
        if (!$this->groups) {
            $searchCriteria = $this->prepareGroupSearchCriteria()->create();
            $attributeGroupSearchResult = $this->attributeGroupRepository->getList($searchCriteria);
            foreach ($attributeGroupSearchResult->getItems() as $group) {
                $this->groups[$this->calculateGroupCode($group)] = $group;
            }
        }

        return $this->groups;
    }

    /**
     * Initialize attribute group search criteria with filters.
     *
     * @return SearchCriteriaBuilder
     */
    protected function prepareGroupSearchCriteria()
    {
        return $this->searchCriteriaBuilder->addFilter(
            AttributeGroupInterface::ATTRIBUTE_SET_ID,
            $this->getAttributeSetId()
        );
    }

    /**
     * Return current attribute set id
     *
     * @return int|null
     */
    protected function getAttributeSetId()
    {
        return $this->locator->getProduct()->getAttributeSetId();
    }

    /**
     * Retrieve attributes
     *
     * @return ProductAttributeInterface[]
     */
    protected function getAttributes()
    {
        if (!$this->attributes) {
            foreach ($this->getGroups() as $group) {
                $this->attributes[$this->calculateGroupCode($group)] = $this->loadAttributes($group);
            }
        }

        return $this->attributes;
    }

    /**
     * Loading product attributes from group
     *
     * @param AttributeGroupInterface $group
     * @return ProductAttributeInterface[]
     */
    protected function loadAttributes(AttributeGroupInterface $group)
    {
        $attributes = [];
        $sortOrder = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeGroupInterface::GROUP_ID, $group->getAttributeGroupId())
            ->addFilter(ProductAttributeInterface::IS_VISIBLE, 1)
            ->addSortOrder($sortOrder)
            ->create();
        $groupAttributes = $this->attributeRepository->getList($searchCriteria)->getItems();
        $productType = $this->getTypeProduct();
        foreach ($groupAttributes as $attribute) {
            $applyTo = $attribute->getApplyTo();
            $isRelated = !$applyTo || in_array($productType, $applyTo);
            if ($isRelated) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Get attribute codes of prev set
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPrevSetAttributes()
    {
        if ($this->prevSetAttributes === null) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('attribute_set_id', $this->getPrevSetId())
                ->create();
            $attributes = $this->attributeRepository->getList($searchCriteria)->getItems();
            $this->prevSetAttributes = [];
            foreach ($attributes as $attribute) {
                $this->prevSetAttributes[] = $attribute->getAttributeCode();
            }
        }

        return $this->prevSetAttributes;
    }

    /**
     * Initial meta setup
     *
     * @param ProductAttributeInterface $attribute
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setupMetaProperties(ProductAttributeInterface $attribute)
    {
        $meta = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType' => $attribute->getFrontendInput(),
                        'formElement' => $this->getFormElementsMapValue($attribute->getFrontendInput()),
                        'visible' => $attribute->getIsVisible(),
                        'required' => $attribute->getIsRequired(),
                        'notice' => $attribute->getNote(),
                        'default' => $attribute->getDefaultValue(),
                        'label' => __($attribute->getDefaultFrontendLabel()),
                    ],
                ],
            ],
        ];
        foreach ($meta as $key => $value) {
            if ($value === null) {
                unset($meta[$key]);
            }
        }

        // TODO: Refactor to $attribute->getOptions() when MAGETWO-48289 is done
        $attributeModel = $this->getAttributeModel($attribute);
        if ($attributeModel->usesSource()) {
            $meta['arguments']['data']['config']['options'] = $attributeModel->getSource()->getAllOptions();
        }

        $meta = $this->addWysiwyg($attribute, $meta);
        $meta = $this->customizeCheckbox($attribute, $meta);
        $meta = $this->customizePriceAttribute($attribute, $meta);

        return $meta;
    }

    /**
     * Customize checkboxes
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function customizeCheckbox(ProductAttributeInterface $attribute, array $meta)
    {
        if ($attribute->getFrontendInput() === 'boolean') {
            $meta['arguments']['data']['config']['prefer'] = 'toggle';
            $meta['arguments']['data']['config']['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
        }

        return $meta;
    }

    /**
     * Customize attribute that has price type
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function customizePriceAttribute(ProductAttributeInterface $attribute, array $meta)
    {
        if ($attribute->getFrontendInput() === 'price') {
            $meta['arguments']['data']['config']['addbefore'] = $this->locator->getStore()
                ->getBaseCurrency()
                ->getCurrencySymbol();
        }

        return $meta;
    }

    /**
     * Add wysiwyg properties
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function addWysiwyg(ProductAttributeInterface $attribute, array $meta)
    {
        if (!$attribute->getIsWysiwygEnabled()) {
            return $meta;
        }

        $meta['arguments']['data']['config']['formElement'] = WysiwygElement::NAME;
        $meta['arguments']['data']['config']['wysiwyg'] = true;

        return $meta;
    }

    /**
     * Retrieve form element
     *
     * @param string $value
     * @return mixed
     */
    protected function getFormElementsMapValue($value)
    {
        $valueMap = $this->formElementMapper->getMappings();

        return isset($valueMap[$value]) ? $valueMap[$value] : $value;
    }

    /**
     * Set attribute to loaded data
     *
     * @param array $data
     * @param string $attributeCode
     * @return $this
     */
    protected function setAttributeValueToData(array $data, $attributeCode)
    {
        $product = $this->locator->getProduct();
        $productId = $product->getId();
        $prevSetId = $this->getPrevSetId();
        $notUsed = !$prevSetId || ($prevSetId && !in_array($attributeCode, $this->getPrevSetAttributes()));

        if ($productId && $notUsed) {
            if (null !== ($value = $this->getValue($attributeCode))) {
                $data[$productId][self::DATA_SOURCE_DEFAULT][$attributeCode] = $value;
            }
        }

        return $data;
    }

    /**
     * Retrieve attribute value
     *
     * @param string $attributeCode
     * @return mixed
     */
    protected function getValue($attributeCode)
    {
        /** @var Product $product */
        $product = $this->locator->getProduct();

        return $product->getData($attributeCode);
    }


    /**
     * Retrieve scope label
     *
     * @param ProductAttributeInterface $attribute
     * @return \Magento\Framework\Phrase|string
     */
    protected function getScopeLabel(ProductAttributeInterface $attribute)
    {
        if (
            $this->storeManager->isSingleStoreMode()
            || $attribute->getFrontendInput() === AttributeInterface::FRONTEND_INPUT
        ) {
            return '';
        }

        switch ($attribute->getScope()) {
            case ProductAttributeInterface::SCOPE_GLOBAL_TEXT:
                return __('[GLOBAL]');
            case ProductAttributeInterface::SCOPE_WEBSITE_TEXT:
                return __('[WEBSITE]');
            case ProductAttributeInterface::SCOPE_STORE_TEXT:
                return __('[STORE VIEW]');
        }

        return '';
    }

    /**
     * Whether attribute can have default value
     *
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    protected function canDisplayUseDefault(ProductAttributeInterface $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        /** @var Product $product */
        $product = $this->locator->getProduct();

        if (isset($this->canDisplayUseDefault[$attributeCode])) {
            return $this->canDisplayUseDefault[$attributeCode];
        }

        return $this->canDisplayUseDefault[$attributeCode] = (
            ($attribute->getScope() != ProductAttributeInterface::SCOPE_GLOBAL_TEXT)
            && $product
            && $product->getId()
            && $product->getStoreId()
        );
    }

    /**
     * Check default value usage fact
     *
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    protected function usedDefault(ProductAttributeInterface $attribute)
    {
        /** @var Product $product */
        $product = $this->locator->getProduct();
        $productId = $product->getId();
        $attributeCode = $attribute->getAttributeCode();
        $defaultValue = $product->getAttributeDefaultValue($attributeCode);

        if (isset($this->usedDefault[$productId][$attributeCode])) {
            return $this->usedDefault[$productId][$attributeCode];
        }

        if (!$product->getExistsStoreValueFlag($attributeCode)) {
            return $this->usedDefault[$productId][$attributeCode] = true;
        } elseif ($product->getData($attributeCode) == $defaultValue &&
            $product->getStoreId() != \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ) {
            return $this->usedDefault[$productId][$attributeCode] = false;
        }

        if ($defaultValue === false && !$attribute->getIsRequired() && $product->getData($attributeCode)) {
            return $this->usedDefault[$productId][$attributeCode] = false;
        }

        return $this->usedDefault[$productId][$attributeCode] = ($defaultValue === false);
    }

    /**
     * Check if attribute scope is global.
     *
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    private function isScopeGlobal($attribute)
    {
        return ($attribute->getScope() == ProductAttributeInterface::SCOPE_GLOBAL_TEXT);
    }

    /**
     * Load attribute model by attribute data object.
     *
     * TODO: This method should be eliminated when all missing service methods are implemented
     *
     * @param ProductAttributeInterface $attribute
     * @return EavAttribute
     */
    private function getAttributeModel($attribute)
    {
        return $this->eavAttributeFactory->create()->load($attribute->getAttributeId());
    }

    /**
     * Calculate group code based on group name.
     *
     * TODO: This logic is copy-pasted from \Magento\Eav\Model\Entity\Attribute\Group::beforeSave
     * TODO: and should be moved to a separate service, which will allow two-way conversion groupName <=> groupCode
     * TODO: Remove after MAGETWO-48290 is complete
     *
     * @param AttributeGroupInterface $group
     * @return string
     */
    private function calculateGroupCode(AttributeGroupInterface $group)
    {
        $groupName = $group->getAttributeGroupName();
        $attributeGroupCode = trim(
            preg_replace(
                '/[^a-z0-9]+/',
                '-',
                $this->translitFilter->filter(strtolower($groupName))
            ),
            '-'
        );
        if ($attributeGroupCode == 'images') {
            $attributeGroupCode = 'image-management';
        }
        if (empty($attributeGroupCode)) {
            // in the following code md5 is not used for security purposes
            $attributeGroupCode = md5($groupName);
        }
        return $attributeGroupCode;
    }
}
