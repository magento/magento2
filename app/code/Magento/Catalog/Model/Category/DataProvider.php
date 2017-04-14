<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\Backend\Image as ImageBackendModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;

/**
 * Class DataProvider
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var string
     */
    protected $requestScopeFieldName = 'store';

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    protected $metaProperties = [
        'dataType' => 'frontend_input',
        'visible' => 'is_visible',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'multiline_count',
    ];

    /**
     * Form element mapping
     *
     * @var array
     */
    protected $formElement = [
        'text' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * Elements with use config setting
     *
     * @var array
     */
    protected $elementsWithUseConfigSetting = [
        'available_sort_by',
        'default_sort_by',
        'filter_price_range',
    ];

    /**
     * List of fields that should not be added into the form
     *
     * @var array
     */
    protected $ignoreFields = [
        'products_position',
        'position'
    ];

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var Filesystem
     */
    private $fileInfo;

    /**
     * DataProvider constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EavValidationRules $eavValidationRules
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param Config $eavConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CategoryFactory $categoryFactory
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        Config $eavConfig,
        \Magento\Framework\App\RequestInterface $request,
        CategoryFactory $categoryFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $categoryCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->categoryFactory = $categoryFactory;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = $this->prepareMeta($meta);

        $category = $this->getCurrentCategory();

        if ($category) {
            $meta = $this->addUseDefaultValueCheckbox($category, $meta);
            $meta = $this->resolveParentInheritance($category, $meta);
        }

        return $meta;
    }

    /**
     * @param Category $category
     * @param array $meta
     * @return array
     */
    private function addUseDefaultValueCheckbox(Category $category, array $meta)
    {
        /** @var EavAttributeInterface $attribute */
        foreach ($category->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $canDisplayUseDefault = $attribute->getScope() != EavAttributeInterface::SCOPE_GLOBAL_TEXT
                && $category->getId()
                && $category->getStoreId();
            $attributePath = $this->getArrayManager()->findPath($attributeCode, $meta);

            if (!$attributePath
                || !$canDisplayUseDefault
                || in_array($attributeCode, $this->elementsWithUseConfigSetting)
            ) {
                continue;
            }

            $meta = $this->getArrayManager()->merge(
                [$attributePath, 'arguments/data/config'],
                $meta,
                [
                    'service' => [
                        'template' => 'ui/form/element/helper/service',
                    ],
                    'disabled' => !$this->getScopeOverriddenValue()->containsValue(
                        CategoryInterface::class,
                        $category,
                        $attributeCode,
                        $this->request->getParam($this->requestScopeFieldName, Store::DEFAULT_STORE_ID)
                    )
                ]
            );
        }

        return $meta;
    }

    /**
     * Removes not necessary inheritance fields
     *
     * @param Category $category
     * @param array $meta
     * @return array
     */
    private function resolveParentInheritance(Category $category, array $meta)
    {
        if (!$category->getParentId() || !$this->getArrayManager()->findPath('custom_use_parent_settings', $meta)) {
            return $meta;
        }

        $meta = $this->getArrayManager()->merge(
            [$this->getArrayManager()->findPath('custom_use_parent_settings', $meta), 'arguments/data/config'],
            $meta,
            ['visible' => false]
        );

        return $meta;
    }

    /**
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta($meta)
    {
        $meta = array_replace_recursive($meta, $this->prepareFieldsMeta(
            $this->getFieldsMap(),
            $this->getAttributesMeta($this->eavConfig->getEntityType('catalog_category'))
        ));

        return $meta;
    }

    /**
     * Prepare fields meta based on xml declaration of form and fields metadata
     *
     * @param array $fieldsMap
     * @param array $fieldsMeta
     * @return array
     */
    private function prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }
        return $result;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $category = $this->getCurrentCategory();
        if ($category) {
            $categoryData = $category->getData();
            $categoryData = $this->addUseConfigSettings($categoryData);
            $categoryData = $this->filterFields($categoryData);
            $categoryData = $this->convertValues($category, $categoryData);

            $this->loadedData[$category->getId()] = $categoryData;
        }
        return $this->loadedData;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var EavAttribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code][$metaName] = $value;
                if ('frontend_input' === $origName) {
                    $meta[$code]['formElement'] = isset($this->formElement[$value])
                        ? $this->formElement[$value]
                        : $value;
                }
                if ($attribute->usesSource()) {
                    $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]);
            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }

            $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
            $meta[$code]['componentType'] = Field::NAME;
        }

        $result = [];
        foreach ($meta as $key => $item) {
            $result[$key] = $item;
            $result[$key]['sortOrder'] = 0;
        }

        $result = $this->getDefaultMetaData($result);

        return $result;
    }

    /**
     * Add use config settings
     *
     * @param array $categoryData
     * @return array
     */
    protected function addUseConfigSettings($categoryData)
    {
        foreach ($this->elementsWithUseConfigSetting as $elementsWithUseConfigSetting) {
            if (!isset($categoryData['use_config'][$elementsWithUseConfigSetting])) {
                if (!isset($categoryData[$elementsWithUseConfigSetting]) ||
                    ($categoryData[$elementsWithUseConfigSetting] == '')
                ) {
                    $categoryData['use_config'][$elementsWithUseConfigSetting] = true;
                } else {
                    $categoryData['use_config'][$elementsWithUseConfigSetting] = false;
                }
            }
        }
        return $categoryData;
    }

    /**
     * Add use default settings
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param array $categoryData
     * @return array
     * @deprecated
     */
    protected function addUseDefaultSettings($category, $categoryData)
    {
        if ($category->getExistsStoreValueFlag('url_key') ||
            $category->getStoreId() === Store::DEFAULT_STORE_ID
        ) {
            $categoryData['use_default']['url_key'] = false;
        } else {
            $categoryData['use_default']['url_key'] = true;
        }

        return $categoryData;
    }

    /**
     * Get current category
     *
     * @return Category
     * @throws NoSuchEntityException
     */
    public function getCurrentCategory()
    {
        $category = $this->registry->registry('category');
        if ($category) {
            return $category;
        }
        $requestId = $this->request->getParam($this->requestFieldName);
        $requestScope = $this->request->getParam($this->requestScopeFieldName, Store::DEFAULT_STORE_ID);
        if ($requestId) {
            $category = $this->categoryFactory->create();
            $category->setStoreId($requestScope);
            $category->load($requestId);
            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('id', $requestId);
            }
        }
        return $category;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @param EavAttribute $attribute
     * @return string
     */
    public function getScopeLabel(EavAttribute $attribute)
    {
        $html = '';
        if (!$attribute || $this->storeManager->isSingleStoreMode()
            || $attribute->getFrontendInput() === AttributeInterface::FRONTEND_INPUT
        ) {
            return $html;
        }
        if ($attribute->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }

        return $html;
    }

    /**
     * Filter fields
     *
     * @param array $categoryData
     * @return array
     */
    protected function filterFields($categoryData)
    {
        return array_diff_key($categoryData, array_flip($this->ignoreFields));
    }

    /**
     * Converts category image data to acceptable for rendering format
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param array $categoryData
     * @return array
     */
    private function convertValues($category, $categoryData)
    {
        foreach ($category->getAttributes() as $attributeCode => $attribute) {
            if (!isset($categoryData[$attributeCode])) {
                continue;
            }

            if ($attribute->getBackend() instanceof ImageBackendModel) {
                unset($categoryData[$attributeCode]);

                $fileName = $category->getData($attributeCode);
                if ($this->getFileInfo()->isExist($fileName)) {
                    $stat = $this->getFileInfo()->getStat($fileName);
                    $mime = $this->getFileInfo()->getMimeType($fileName);

                    $categoryData[$attributeCode][0]['name'] = $fileName;
                    $categoryData[$attributeCode][0]['url'] = $category->getImageUrl($attributeCode);
                    $categoryData['image'][0]['size'] = isset($stat) ? $stat['size'] : 0;
                    $categoryData['image'][0]['type'] = $mime;
                }
            }
        }

        return $categoryData;
    }

    /**
     * Category's fields default values
     *
     * @param array $result
     * @return array
     */
    public function getDefaultMetaData($result)
    {
        $result['parent']['default'] = (int)$this->request->getParam('parent');
        $result['use_config.available_sort_by']['default'] = true;
        $result['use_config.default_sort_by']['default'] = true;
        $result['use_config.filter_price_range']['default'] = true;

        return $result;
    }

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' => [
                    'parent',
                    'path',
                    'is_active',
                    'include_in_menu',
                    'name',
                ],
            'content' => [
                    'image',
                    'description',
                    'landing_page',
                ],
            'display_settings' => [
                    'display_mode',
                    'is_anchor',
                    'available_sort_by',
                    'use_config.available_sort_by',
                    'default_sort_by',
                    'use_config.default_sort_by',
                    'filter_price_range',
                    'use_config.filter_price_range',
                ],
            'search_engine_optimization' => [
                    'url_key',
                    'url_key_create_redirect',
                    'url_key_group',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                ],
            'assign_products' => [
                ],
            'design' => [
                    'custom_use_parent_settings',
                    'custom_apply_to_products',
                    'custom_design',
                    'page_layout',
                    'custom_layout_update',
                ],
            'schedule_design_update' => [
                    'custom_design_from',
                    'custom_design_to',
                ],
            'category_view_optimization' => [
                ],
            'category_permissions' => [
                ],
        ];
    }

    /**
     * Retrieve scope overridden value
     *
     * @return ScopeOverriddenValue
     * @deprecated
     */
    private function getScopeOverriddenValue()
    {
        if (null === $this->scopeOverriddenValue) {
            $this->scopeOverriddenValue = \Magento\Framework\App\ObjectManager::getInstance()->get(
                ScopeOverriddenValue::class
            );
        }

        return $this->scopeOverriddenValue;
    }

    /**
     * Retrieve array manager
     *
     * @return ArrayManager
     * @deprecated
     */
    private function getArrayManager()
    {
        if (null === $this->arrayManager) {
            $this->arrayManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                ArrayManager::class
            );
        }

        return $this->arrayManager;
    }

    /**
     * Get FileInfo instance
     *
     * @return FileInfo
     *
     * @deprecated
     */
    private function getFileInfo()
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = ObjectManager::getInstance()->get(FileInfo::class);
        }
        return $this->fileInfo;
    }
}
