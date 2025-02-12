<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model;

use Magento\Catalog\Model\Config;
use Magento\CatalogSearch\Model\Advanced\ProductCollectionPrepareStrategyProvider;
use Magento\CatalogSearch\Model\Search\ItemCollectionProviderInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection as ProductCollection;
use Magento\CatalogSearch\Model\ResourceModel\AdvancedFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\Model\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Catalog advanced search model
 *
 * @method int getEntityTypeId()
 * @method \Magento\CatalogSearch\Model\Advanced setEntityTypeId(int $value)
 * @method int getAttributeSetId()
 * @method \Magento\CatalogSearch\Model\Advanced setAttributeSetId(int $value)
 * @method string getTypeId()
 * @method \Magento\CatalogSearch\Model\Advanced setTypeId(string $value)
 * @method string getSku()
 * @method \Magento\CatalogSearch\Model\Advanced setSku(string $value)
 * @method int getHasOptions()
 * @method \Magento\CatalogSearch\Model\Advanced setHasOptions(int $value)
 * @method int getRequiredOptions()
 * @method \Magento\CatalogSearch\Model\Advanced setRequiredOptions(int $value)
 * @method string getCreatedAt()
 * @method \Magento\CatalogSearch\Model\Advanced setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\CatalogSearch\Model\Advanced setUpdatedAt(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Advanced extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var array
     */
    protected $_searchCriterias = [];

    /**
     * @var ProductCollection
     */
    protected $_productCollection;

    /**
     * @deprecated 101.0.2
     * @var Config
     */
    protected $_catalogConfig;

    /**
     * @var Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var AttributeCollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @deprecated
     * @see $collectionProvider
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ItemCollectionProviderInterface
     */
    private $collectionProvider;

    /**
     * @var ProductCollectionPrepareStrategyProvider|null
     */
    private $productCollectionPrepareStrategyProvider;

    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $registry
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param Config $catalogConfig
     * @param CurrencyFactory $currencyFactory
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollectionFactory
     * @param AdvancedFactory $advancedFactory
     * @param array $data
     * @param ItemCollectionProviderInterface|null $collectionProvider
     * @param ProductCollectionPrepareStrategyProvider|null $productCollectionPrepareStrategyProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AttributeCollectionFactory $attributeCollectionFactory,
        Visibility $catalogProductVisibility,
        Config $catalogConfig,
        CurrencyFactory $currencyFactory,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        AdvancedFactory $advancedFactory,
        array $data = [],
        ?ItemCollectionProviderInterface $collectionProvider = null,
        ?ProductCollectionPrepareStrategyProvider $productCollectionPrepareStrategyProvider = null
    ) {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogConfig = $catalogConfig;
        $this->_currencyFactory = $currencyFactory;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->collectionProvider = $collectionProvider;
        $this->productCollectionPrepareStrategyProvider = $productCollectionPrepareStrategyProvider
            ?: ObjectManager::getInstance()->get(ProductCollectionPrepareStrategyProvider::class);
        parent::__construct(
            $context,
            $registry,
            $advancedFactory->create(),
            $this->resolveProductCollection(),
            $data
        );
    }

    /**
     * Add advanced search filters to product collection
     *
     * @param array $values
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addFilters($values)
    {
        $attributes = $this->getAttributes();
        $allConditions = [];

        foreach ($attributes as $attribute) {
            /* @var $attribute Attribute */
            if (!isset($values[$attribute->getAttributeCode()])) {
                continue;
            }

            $value = $values[$attribute->getAttributeCode()];

            if (($attribute->getFrontendInput() == 'text' || $attribute->getFrontendInput() == 'textarea')
                && (!is_string($value) || !trim($value))
            ) {
                continue;
            }

            $preparedSearchValue = $this->getPreparedSearchCriteria($attribute, $value);
            if (false === $preparedSearchValue) {
                continue;
            }
            $this->addSearchCriteria($attribute, $preparedSearchValue);

            if ($attribute->getAttributeCode() == 'price') {
                foreach ($value as $key => $element) {
                    if (is_array($element)) {
                        $value[$key] = 0;
                    }
                }

                $rate = 1;
                $store = $this->_storeManager->getStore();
                $currency = $store->getCurrentCurrencyCode();
                if ($currency != $store->getBaseCurrencyCode()) {
                    $rate = $store->getBaseCurrency()->getRate($currency);
                }

                $value['from'] = (isset($value['from']) && is_numeric($value['from']))
                    ? (float)$value['from'] / $rate
                    : '';
                $value['to'] = (isset($value['to']) && is_numeric($value['to']))
                    ? (float)$value['to'] / $rate
                    : '';
            }

            if ($attribute->getBackendType() == 'datetime') {
                $value['from'] = (isset($value['from']) && !empty($value['from']))
                    ? date('Y-m-d\TH:i:s\Z', strtotime($value['from']))
                    : '';
                $value['to'] = (isset($value['to']) && !empty($value['to']))
                    ? date('Y-m-d\TH:i:s\Z', strtotime($value['to']))
                    : '';
            }

            if ($attribute->getAttributeCode() === 'sku') {
                $value = mb_strtolower($value);
            }

            $condition = $this->_getResource()->prepareCondition(
                $attribute,
                $value,
                $this->getProductCollection()
            );
            if ($condition === false) {
                continue;
            }

            $table = $attribute->getBackend()->getTable();
            if ($attribute->getBackendType() == 'static') {
                $attributeId = $attribute->getAttributeCode();
            } else {
                $attributeId = $attribute->getId();
            }
            $allConditions[$table][$attributeId] = $condition;
        }
        if ($allConditions) {
            $this->_registry->register('advanced_search_conditions', $allConditions);
            $this->getProductCollection()->addFieldsToFilter($allConditions);
        } else {
            throw new LocalizedException(__('Enter a search term and try again.'));
        }

        return $this;
    }

    /**
     * Retrieve array of attributes used in advanced search
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getAttributes()
    {
        $attributes = $this->getData('attributes');
        if ($attributes === null) {
            $product = $this->_productFactory->create();
            $attributes = $this->_attributeCollectionFactory
                ->create()
                ->addHasOptionsFilter()
                ->addDisplayInAdvancedSearchFilter()
                ->addStoreLabel($this->_storeManager->getStore()->getId())
                ->setOrder('main_table.attribute_id', 'asc')
                ->load();
            foreach ($attributes as $attribute) {
                $attribute->setEntity($product->getResource());
            }
            $this->setData('attributes', $attributes);
        }
        return $attributes;
    }

    /**
     * Retrieve advanced search product collection
     *
     * @return Collection
     */
    public function getProductCollection()
    {
        if ($this->_productCollection === null) {
            $collection = $this->resolveProductCollection();
            $this->prepareProductCollection($collection);
            if (!$collection) {
                return $collection;
            }
            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    /**
     * Resolve product collection.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection
     */
    private function resolveProductCollection()
    {
        return (null === $this->collectionProvider)
            ? $this->productCollectionFactory->create()
            : $this->collectionProvider->getCollection();
    }

    /**
     * Prepare product collection
     *
     * @param Collection $collection
     * @return $this
     */
    public function prepareProductCollection($collection)
    {
        $this->productCollectionPrepareStrategyProvider->getStrategy()->prepare($collection);

        return $this;
    }

    /**
     * Add search criteria.
     *
     * @param EntityAttribute $attribute
     * @param mixed $value
     * @return void
     */
    protected function addSearchCriteria($attribute, $value)
    {
        if (!empty($value)) {
            $this->_searchCriterias[] = ['name' => $attribute->getStoreLabel(), 'value' => $value];
        }
    }

    /**
     * Add data about search criteria to object state
     *
     * @param EntityAttribute $attribute
     * @param mixed $value
     *
     * @return string|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws LocalizedException
     * @todo: Move this code to block
     */
    protected function getPreparedSearchCriteria($attribute, $value)
    {
        $from = null;
        $to = null;
        if (is_array($value)) {
            foreach ($value as $key => $element) {
                if (is_array($element)) {
                    $value[$key] = '';
                }
            }
            if (isset($value['from']) && isset($value['to'])) {
                if (!empty($value['from']) || !empty($value['to'])) {
                    $from = '';
                    $to = '';

                    if (isset($value['currency'])) {
                        /** @var $currencyModel Currency */
                        $currencyModel = $this->_currencyFactory->create()->load($value['currency']);
                        $from = $currencyModel->format($value['from'], [], false);
                        $to = $currencyModel->format($value['to'], [], false);
                    } else {
                        $currencyModel = null;
                    }

                    if (strlen($value['from']) > 0 && strlen($value['to']) > 0) {
                        // -
                        $value = sprintf(
                            '%s - %s',
                            $currencyModel ? $from : $value['from'],
                            $currencyModel ? $to : $value['to']
                        );
                    } elseif (strlen($value['from']) > 0) {
                        // and more
                        $value = __('%1 and greater', $currencyModel ? $from : $value['from']);
                    } elseif (strlen($value['to']) > 0) {
                        // to
                        $value = __('up to %1', $currencyModel ? $to : $value['to']);
                    }
                } else {
                    return '';
                }
            }
        }

        if (($attribute->getFrontendInput() == 'select' ||
                $attribute->getFrontendInput() == 'multiselect') && is_array($value)
        ) {
            foreach ($value as $key => $val) {
                $value[$key] = $attribute->getSource()->getOptionText($val);

                if (is_array($value[$key])) {
                    $value[$key] = $value[$key]['label'];
                }
            }
            $value = implode(', ', $value);
        } elseif ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
            $value = $attribute->getSource()->getOptionText($value);
            if (is_array($value)) {
                $value = $value['label'];
            }
        } elseif ($attribute->getFrontendInput() == 'boolean') {
            if (is_numeric($value)) {
                $value = $value == 1 ? __('Yes') : __('No');
            } else {
                $value = false;
            }
        }

        return $value;
    }

    /**
     * Returns prepared search criterias in text
     *
     * @return array
     */
    public function getSearchCriterias()
    {
        return $this->_searchCriterias;
    }
}
