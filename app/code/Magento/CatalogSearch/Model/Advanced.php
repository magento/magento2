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
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog advanced search model
 *
 * @method \Magento\CatalogSearch\Model\Resource\Advanced getResource()
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
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Model;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Resource\Eav\Attribute;
use Magento\Catalog\Model\Resource\Eav\Resource\Product\Attribute\Collection as AttributeCollection;
use Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Helper\Data;
use Magento\CatalogSearch\Model\Advanced as ModelAdvanced;
use Magento\CatalogSearch\Model\Resource\Advanced\Collection;
use Magento\CatalogSearch\Model\Resource\EngineInterface;
use Magento\CatalogSearch\Model\Resource\EngineProvider;
use Magento\Core\Exception;
use Magento\Core\Model\AbstractModel;
use Magento\Model\Context;
use Magento\Registry;
use Magento\Core\Model\StoreManagerInterface;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;

class Advanced extends AbstractModel
{
    /**
     * User friendly search criteria list
     *
     * @var array
     */
    protected $_searchCriterias = array();

    /**
     * Current search engine
     *
     * @var EngineInterface
     */
    protected $_engine;

    /**
     * Found products collection
     *
     * @var Collection
     */
    protected $_productCollection;

    /**
     * Initialize dependencies
     *
     * @var Config
     */
    protected $_catalogConfig;

    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Attribute collection factory
     *
     * @var CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Product factory
     *
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * Currency factory
     *
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $attributeCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param Config $catalogConfig
     * @param EngineProvider $engineProvider
     * @param Data $helper
     * @param CurrencyFactory $currencyFactory
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $attributeCollectionFactory,
        Visibility $catalogProductVisibility,
        Config $catalogConfig,
        EngineProvider $engineProvider,
        Data $helper,
        CurrencyFactory $currencyFactory,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogConfig = $catalogConfig;
        $this->_engine = $engineProvider->get();
        $this->_currencyFactory = $currencyFactory;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context, $registry, $this->_engine->getResource(), $this->_engine->getResourceCollection(), $data
        );
    }

    /**
     * Retrieve array of attributes used in advanced search
     *
     * @return array
     */
    public function getAttributes()
    {
        /* @var $attributes AttributeCollection */
        $attributes = $this->getData('attributes');
        if (is_null($attributes)) {
            $product = $this->_productFactory->create();
            $attributes = $this->_attributeCollectionFactory->create()
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
     * Add advanced search filters to product collection
     *
     * @param   array $values
     * @return  $this
     * @throws Exception
     */
    public function addFilters($values)
    {
        $attributes     = $this->getAttributes();
        $hasConditions  = false;
        $allConditions  = array();

        foreach ($attributes as $attribute) {
            /* @var $attribute Attribute */
            if (!isset($values[$attribute->getAttributeCode()])) {
                continue;
            }
            $value = $values[$attribute->getAttributeCode()];

            if ($attribute->getAttributeCode() == 'price') {
                $value['from'] = isset($value['from']) ? trim($value['from']) : '';
                $value['to'] = isset($value['to']) ? trim($value['to']) : '';
                if (is_numeric($value['from']) || is_numeric($value['to'])) {
                    if (!empty($value['currency'])) {
                        $rate = $this->_storeManager->getStore()->getBaseCurrency()->getRate($value['currency']);
                    } else {
                        $rate = 1;
                    }
                    if ($this->_getResource()->addRatedPriceFilter(
                        $this->getProductCollection(), $attribute, $value, $rate)
                    ) {
                        $hasConditions = true;
                        $this->_addSearchCriteria($attribute, $value);
                    }
                }
            } else if ($attribute->isIndexable()) {
                if (!is_string($value) || strlen($value) != 0) {
                    if ($this->_getResource()->addIndexableAttributeModifiedFilter(
                        $this->getProductCollection(), $attribute, $value)) {
                        $hasConditions = true;
                        $this->_addSearchCriteria($attribute, $value);
                    }
                }
            } else {
                $condition = $this->_getResource()->prepareCondition($attribute, $value, $this->getProductCollection());
                if ($condition === false) {
                    continue;
                }

                $this->_addSearchCriteria($attribute, $value);

                $table = $attribute->getBackend()->getTable();
                if ($attribute->getBackendType() == 'static'){
                    $attributeId = $attribute->getAttributeCode();
                } else {
                    $attributeId = $attribute->getId();
                }
                $allConditions[$table][$attributeId] = $condition;
            }
        }
        if ($allConditions) {
            $this->getProductCollection()->addFieldsToFilter($allConditions);
        } else if (!$hasConditions) {
            throw new Exception(__('Please specify at least one search term.'));
        }

        return $this;
    }

    /**
     * Add data about search criteria to object state
     *
     * @param   EntityAttribute $attribute
     * @param   mixed $value
     * @return  $this
     */
    protected function _addSearchCriteria($attribute, $value)
    {
        $name = $attribute->getStoreLabel();

        if (is_array($value)) {
            if (isset($value['from']) && isset($value['to'])) {
                if (!empty($value['from']) || !empty($value['to'])) {
                    if (isset($value['currency'])) {
                        /** @var $currencyModel Currency */
                        $currencyModel = $this->_currencyFactory->create()->load($value['currency']);
                        $from = $currencyModel->format($value['from'], array(), false);
                        $to = $currencyModel->format($value['to'], array(), false);
                    } else {
                        $currencyModel = null;
                    }

                    if (strlen($value['from']) > 0 && strlen($value['to']) > 0) {
                        // -
                        $value = sprintf('%s - %s',
                            ($currencyModel ? $from : $value['from']), ($currencyModel ? $to : $value['to']));
                    } elseif (strlen($value['from']) > 0) {
                        // and more
                        $value = __('%1 and greater', ($currencyModel ? $from : $value['from']));
                    } elseif (strlen($value['to']) > 0) {
                        // to
                        $value = __('up to %1', ($currencyModel ? $to : $value['to']));
                    }
                } else {
                    return $this;
                }
            }
        }

        if (($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect')
            && is_array($value)
        ) {
            foreach ($value as $key => $val){
                $value[$key] = $attribute->getSource()->getOptionText($val);

                if (is_array($value[$key])) {
                    $value[$key] = $value[$key]['label'];
                }
            }
            $value = implode(', ', $value);
        } else if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
            $value = $attribute->getSource()->getOptionText($value);
            if (is_array($value))
                $value = $value['label'];
        } else if ($attribute->getFrontendInput() == 'boolean') {
            $value = $value == 1
                ? __('Yes')
                : __('No');
        }

        $this->_searchCriterias[] = array('name' => $name, 'value' => $value);
        return $this;
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

    /**
     * Retrieve advanced search product collection
     *
     * @return Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $collection = $this->_engine->getAdvancedResultCollection();
            $this->prepareProductCollection($collection);
            if (!$collection) {
                return $collection;
            }
            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    /**
     * Prepare product collection
     *
     * @param Collection $collection
     * @return $this
     */
    public function prepareProductCollection($collection)
    {
        $collection->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->setStore($this->_storeManager->getStore())
            ->addMinimalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->setVisibility($this->_catalogProductVisibility->getVisibleInSearchIds());

        return $this;
    }
}
