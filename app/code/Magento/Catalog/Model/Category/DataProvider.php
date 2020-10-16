<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\Backend\Image as ImageBackendModel;
use Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Source\SpecificSourceInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

/**
 * Category form data provider.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @since 101.0.0
 */
class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @var string
     * @since 101.0.0
     */
    protected $requestScopeFieldName = 'store';

    /**
     * @var array
     * @since 101.0.0
     */
    protected $loadedData;

    /**
     * EAV attribute properties to fetch from meta storage
     *
     * @var array
     * @since 101.0.0
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

    private $boolMetaProperties = ['visible', 'required'];

    /**
     * Form element mapping
     *
     * @var array
     * @since 101.0.0
     */
    protected $formElement = [
        'text' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * Elements with use config setting
     *
     * @var array
     * @since 101.0.0
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
     * @since 101.0.0
     */
    protected $ignoreFields = [
        'products_position',
        'position'
    ];

    /**
     * Elements with currency symbol
     *
     * @var array
     */
    private $elementsWithCurrencySymbol = [
        'filter_price_range',
    ];

    /**
     * @var EavValidationRules
     * @since 101.0.0
     */
    protected $eavValidationRules;

    /**
     * @var Registry
     * @since 101.0.0
     */
    protected $registry;

    /**
     * @var RequestInterface
     * @since 101.0.0
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
     * @var DataInterfaceFactory
     */
    private $uiConfigFactory;

    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ArrayUtils
     */
    private $arrayUtils;

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * @var AuthorizationInterface
     */
    private $auth;

    /**
     * @var Image
     */
    private $categoryImage;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EavValidationRules $eavValidationRules
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param Config $eavConfig
     * @param RequestInterface $request
     * @param CategoryFactory $categoryFactory
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     * @param AuthorizationInterface|null $auth
     * @param ArrayUtils|null $arrayUtils
     * @param ScopeOverriddenValue|null $scopeOverriddenValue
     * @param ArrayManager|null $arrayManager
     * @param FileInfo|null $fileInfo
     * @param Image|null $categoryImage
     * @param DataInterfaceFactory|null $uiConfigFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Config $eavConfig,
        RequestInterface $request,
        CategoryFactory $categoryFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null,
        ?AuthorizationInterface $auth = null,
        ?ArrayUtils $arrayUtils = null,
        ScopeOverriddenValue $scopeOverriddenValue = null,
        ArrayManager $arrayManager = null,
        FileInfo $fileInfo = null,
        ?Image $categoryImage = null,
        ?DataInterfaceFactory $uiConfigFactory = null
    ) {
        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $categoryCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->categoryFactory = $categoryFactory;
        $this->auth = $auth ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->arrayUtils = $arrayUtils ?? ObjectManager::getInstance()->get(ArrayUtils::class);
        $this->scopeOverriddenValue = $scopeOverriddenValue ?:
            ObjectManager::getInstance()->get(ScopeOverriddenValue::class);
        $this->arrayManager = $arrayManager ?: ObjectManager::getInstance()->get(ArrayManager::class);
        $this->fileInfo = $fileInfo ?: ObjectManager::getInstance()->get(FileInfo::class);
        $this->categoryImage = $categoryImage ?? ObjectManager::getInstance()->get(Image::class);
        $this->uiConfigFactory = $uiConfigFactory ?? ObjectManager::getInstance()->create(
            DataInterfaceFactory::class,
            ['instanceName' => \Magento\Ui\Config\Data::class]
        );

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * @inheritdoc
     * @since 102.0.0
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = $this->prepareMeta($meta);

        $category = $this->getCurrentCategory();

        if ($category) {
            $meta = $this->addUseDefaultValueCheckbox($category, $meta);
        }
        // Default and custom settings
        return $meta;
    }

    /**
     * Disable fields if they are using default values.
     *
     * @param Category $category
     * @param array $meta
     * @return array
     */
    private function addUseDefaultValueCheckbox(Category $category, array $meta): array
    {
        /** @var EavAttributeInterface $attribute */
        foreach ($category->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $canDisplayUseDefault = $attribute->getScope() != EavAttributeInterface::SCOPE_GLOBAL_TEXT
                && $category->getId()
                && $category->getStoreId();
            $attributePath = $this->arrayManager->findPath($attributeCode, $meta);

            if (!$attributePath
                || !$canDisplayUseDefault
                || in_array($attributeCode, $this->elementsWithUseConfigSetting)
            ) {
                continue;
            }

            $meta = $this->arrayManager->merge(
                [$attributePath, 'arguments/data/config'],
                $meta,
                [
                    'service' => [
                        'template' => 'ui/form/element/helper/service',
                    ],
                    'disabled' => !$this->scopeOverriddenValue->containsValue(
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
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    public function prepareMeta($meta)
    {
        $meta = array_replace_recursive(
            $meta,
            $this->prepareFieldsMeta(
                $this->getFieldsMap(),
                $this->getAttributesMeta($this->eavConfig->getEntityType('catalog_category'))
            )
        );

        return $meta;
    }

    /**
     * Prepare fields meta based on xml declaration of form and fields metadata
     *
     * @param array $fieldsMap
     * @param array $fieldsMeta
     * @return array
     */
    private function prepareFieldsMeta(array $fieldsMap, array $fieldsMeta): array
    {
        $canEditDesign = $this->auth->isAllowed('Magento_Catalog::edit_category_design');

        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $config = $fieldsMeta[$field];
                    if (($fieldSet === 'design' || $fieldSet === 'schedule_design_update') && !$canEditDesign) {
                        $config['required'] = 1;
                        $config['disabled'] = 1;
                        $config['serviceDisabled'] = true;
                    }

                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $config;
                }
            }
        }
        return $result;
    }

    /**
     * Get data
     *
     * @return array
     * @since 101.0.0
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
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 101.0.0
     */
    public function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        $fields = $this->getFields();
        $category = $this->getCurrentCategory();
        /* @var EavAttribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code][$metaName] = $value;
                if (in_array($metaName, $this->boolMetaProperties, true)) {
                    $meta[$code][$metaName] = (bool)$meta[$code][$metaName];
                }
                if ('frontend_input' === $origName) {
                    $meta[$code]['formElement'] = isset($this->formElement[$value])
                        ? $this->formElement[$value]
                        : $value;
                }
                if ($attribute->usesSource()) {
                    $source = $attribute->getSource();
                    $currentCategory = $this->getCurrentCategory();
                    if ($source instanceof SpecificSourceInterface && $currentCategory) {
                        $options = $source->getOptionsFor($currentCategory);
                    } else {
                        $options = $source->getAllOptions();
                    }
                    foreach ($options as &$option) {
                        $option['__disableTmpl'] = true;
                    }
                    $meta[$code]['options'] = $options;
                }
            }

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]);
            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }

            $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
            $meta[$code]['componentType'] = Field::NAME;

            // disable fields
            if ($category) {
                $attributeIsLocked = $category->isLockedAttribute($code);
                $meta[$code]['disabled'] = $attributeIsLocked;
                $hasUseConfigField = (bool)array_search('use_config.' . $code, $fields, true);
                if ($hasUseConfigField && $meta[$code]['disabled']) {
                    $meta['use_config.' . $code]['disabled'] = true;
                }
            }

            if (in_array($code, $this->elementsWithCurrencySymbol, false)) {
                $requestScope = $this->request->getParam(
                    $this->requestScopeFieldName,
                    Store::DEFAULT_STORE_ID
                );

                $meta[$code]['addbefore'] = $this->storeManager->getStore($requestScope)
                    ->getBaseCurrency()
                    ->getCurrencySymbol();
            }
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
     * @since 101.0.0
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
     * @param Category $category
     * @param array $categoryData
     * @return array
     * @deprecated 102.0.0
     * @since 101.0.0
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
     * @since 101.0.0
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
     * @since 101.0.0
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
     * @since 101.0.0
     */
    protected function filterFields($categoryData)
    {
        return array_diff_key($categoryData, array_flip($this->ignoreFields));
    }

    /**
     * Converts category image data to acceptable for rendering format
     *
     * @param Category $category
     * @param array $categoryData
     * @return array
     */
    private function convertValues($category, $categoryData): array
    {
        foreach ($category->getAttributes() as $attributeCode => $attribute) {
            if ($attributeCode === 'custom_layout_update_file') {
                if (!empty($categoryData['custom_layout_update'])) {
                    $categoryData['custom_layout_update_file']
                        = LayoutUpdate::VALUE_USE_UPDATE_XML;
                }
            }
            if (!isset($categoryData[$attributeCode])) {
                continue;
            }

            if ($attribute->getBackend() instanceof ImageBackendModel) {
                unset($categoryData[$attributeCode]);

                $fileName = $category->getData($attributeCode);

                if ($this->fileInfo->isExist($fileName)) {
                    $stat = $this->fileInfo->getStat($fileName);
                    $mime = $this->fileInfo->getMimeType($fileName);

                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $categoryData[$attributeCode][0]['name'] = basename($fileName);

                    $categoryData[$attributeCode][0]['url'] = $this->categoryImage->getUrl($category, $attributeCode);

                    $categoryData[$attributeCode][0]['size'] = $stat['size'];
                    $categoryData[$attributeCode][0]['type'] = $mime;
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
     * @since 101.0.0
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
     * List of fields groups and fields.
     *
     * @return array
     * @since 101.0.0
     */
    protected function getFieldsMap()
    {
        $referenceName = 'category_form';
        $config = $this->uiConfigFactory
            ->create(['componentName' => $referenceName])
            ->get($referenceName);

        if (empty($config)) {
            return [];
        }

        $fieldsMap = [];

        foreach ($config['children'] as $group => $node) {
            // Skip disabled components (required for Commerce Edition)
            if ($node['arguments']['data']['config']['componentDisabled'] ?? false) {
                continue;
            }

            $fields = [];

            foreach ($node['children'] as $childName => $childNode) {
                if (!empty($childNode['children'])) {
                    // <container/> nodes need special handling
                    foreach (array_keys($childNode['children']) as $grandchildName) {
                        $fields[] = $grandchildName;
                    }
                } else {
                    $fields[] = $childName;
                }
            }

            if (!empty($fields)) {
                $fieldsMap[$group] = $fields;
            }
        }

        return $fieldsMap;
    }

    /**
     * Return list of fields names.
     *
     * @return array
     */
    private function getFields(): array
    {
        $fieldsMap = $this->getFieldsMap();
        return $this->arrayUtils->flatten($fieldsMap);
    }
}
