<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Helper\Toolkit;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
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
        'hidden' => 'input',
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
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Toolkit
     */
    private $eavToolkit;
    /**
     * @var Category
     */
    private $category;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EavValidationRules $eavValidationRules
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Registry $registry ,
     * @param Config $eavConfig
     * @param FilterPool $filterPool
     * @param StoreManagerInterface $storeManager
     * @param Toolkit $eavToolkit
     * @param array $meta
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CategoryCollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Registry $registry,
        Config $eavConfig,
        FilterPool $filterPool,
        StoreManagerInterface $storeManager,
        Toolkit $eavToolkit,
        array $meta = [],
        array $data = []
    ) {
        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $categoryCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->filterPool = $filterPool;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->eavToolkit = $eavToolkit;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta['general']['fields'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('catalog_category')
        );
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
        if (!$category) {
            return [];
        } else {
            $categoryData = $category->getData();
            $categoryData = $this->addUseConfigSettings($categoryData);
            $result['general'] = $categoryData;
            $this->loadedData[$category->getId()] = $result;
        }
        return $this->loadedData;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code][$metaName] = ($metaName === 'label') ? __($value) : $value;
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

            $meta[$code]['scope_label'] = $this->getScopeLabel($attribute);
            $meta[$code]['service'] = [
                'template' => 'ui/form/element/helper/service',
                'displayUseDefault' => $this->eavToolkit->canDisplayUseDefault($attribute),
            ];
            $meta[$code]['used_default'] = (int)($this->eavToolkit->canDisplayUseDefault($attribute) && $this->eavToolkit->usedDefault($attribute));
            $meta[$code]['componentType'] = $meta[$code]['formElement'];
            $meta[$code]['code'] = $code;

            if ($this->getModel()->getStoreId() && !$attribute->isScopeGlobal()) {
                $meta[$code]['disabled'] = $meta[$code]['used_default'];
            }
        }

        $result = [];
        foreach ($meta as $key => $item) {
            $result[$key] = $item;
            $result[$key]['sortOrder'] = 0;
        }
        
        return $result;
    }

    /**
     * Add use config settings
     *
     * @param Type $categoryData
     * @return array
     */
    protected function addUseConfigSettings($categoryData)
    {
        foreach ($this->elementsWithUseConfigSetting as $elementsWithUseConfigSetting) {
            if (
                !isset($categoryData[$elementsWithUseConfigSetting]) ||
                ($categoryData[$elementsWithUseConfigSetting] == '')
            ) {
                $categoryData['use_config'][$elementsWithUseConfigSetting] = true;
            }
        }
        return $categoryData;
    }

    /**
     * Get current category
     *
     * @return mixed
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('category');
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getScopeLabel(AbstractAttribute $attribute)
    {
        $html = '';
        if (
            !$attribute || $this->storeManager->isSingleStoreMode()
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
     * Get model
     *
     * @return Category
     */
    public function getModel()
    {
        if (!$this->category && ($category = $this->registry->registry('current_category'))) {
            $this->category = $category;
        }

        return $this->category;
    }
}
