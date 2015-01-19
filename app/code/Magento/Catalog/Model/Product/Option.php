<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Resource\Product\Option\Value\Collection;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Exception;

/**
 * Catalog product option model
 *
 * @method \Magento\Catalog\Model\Resource\Product\Option getResource()
 * @method int getProductId()
 * @method \Magento\Catalog\Model\Product\Option setProductId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends AbstractExtensibleModel implements \Magento\Catalog\Api\Data\ProductCustomOptionInterface
{
    const OPTION_GROUP_TEXT = 'text';

    const OPTION_GROUP_FILE = 'file';

    const OPTION_GROUP_SELECT = 'select';

    const OPTION_GROUP_DATE = 'date';

    const OPTION_TYPE_FIELD = 'field';

    const OPTION_TYPE_AREA = 'area';

    const OPTION_TYPE_FILE = 'file';

    const OPTION_TYPE_DROP_DOWN = 'drop_down';

    const OPTION_TYPE_RADIO = 'radio';

    const OPTION_TYPE_CHECKBOX = 'checkbox';

    const OPTION_TYPE_MULTIPLE = 'multiple';

    const OPTION_TYPE_DATE = 'date';

    const OPTION_TYPE_DATE_TIME = 'date_time';

    const OPTION_TYPE_TIME = 'time';

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @var array
     */
    protected $_values = [];

    /**
     * Catalog product option value
     *
     * @var Option\Value
     */
    protected $_productOptionValue;

    /**
     * Product option factory
     *
     * @var \Magento\Catalog\Model\Product\Option\Type\Factory
     */
    protected $_optionFactory;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @var Option\Validator\Pool
     */
    protected $validatorPool;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Api\CategoryAttributeRepositoryInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param Option\Value $productOptionValue
     * @param Option\Type\Factory $optionFactory
     * @param \Magento\Framework\Stdlib\String $string
     * @param Option\Validator\Pool $validatorPool
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\CategoryAttributeRepositoryInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        Option\Value $productOptionValue,
        \Magento\Catalog\Model\Product\Option\Type\Factory $optionFactory,
        \Magento\Framework\Stdlib\String $string,
        Option\Validator\Pool $validatorPool,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_productOptionValue = $productOptionValue;
        $this->_optionFactory = $optionFactory;
        $this->validatorPool = $validatorPool;
        $this->string = $string;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected function _getResource()
    {
        return $this->_resource ?: parent::_getResource();
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Product\Option');
        parent::_construct();
    }

    /**
     * Add value of option to values array
     *
     * @param Option\Value $value
     * @return $this
     */
    public function addValue(Option\Value $value)
    {
        $this->_values[$value->getId()] = $value;
        return $this;
    }

    /**
     * Get value by given id
     *
     * @param int $valueId
     * @return Option\Value|null
     */
    public function getValueById($valueId)
    {
        if (isset($this->_values[$valueId])) {
            return $this->_values[$valueId];
        }

        return null;
    }

    /**
     * @return ProductCustomOptionValuesInterface[]|null
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * Retrieve value instance
     *
     * @return Option\Value
     */
    public function getValueInstance()
    {
        return $this->_productOptionValue;
    }

    /**
     * Add option for save it
     *
     * @param array $option
     * @return $this
     */
    public function addOption($option)
    {
        $this->_options[] = $option;
        return $this;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set options for array
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Set options to empty array
     *
     * @return $this
     */
    public function unsetOptions()
    {
        $this->_options = [];
        return $this;
    }

    /**
     * Retrieve product instance
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Set product instance
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product = null)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Get group name of option by given option type
     *
     * @param string $type
     * @return string
     */
    public function getGroupByType($type = null)
    {
        if (is_null($type)) {
            $type = $this->getType();
        }
        $optionGroupsToTypes = [
            self::OPTION_TYPE_FIELD => self::OPTION_GROUP_TEXT,
            self::OPTION_TYPE_AREA => self::OPTION_GROUP_TEXT,
            self::OPTION_TYPE_FILE => self::OPTION_GROUP_FILE,
            self::OPTION_TYPE_DROP_DOWN => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_RADIO => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_CHECKBOX => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_MULTIPLE => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_DATE => self::OPTION_GROUP_DATE,
            self::OPTION_TYPE_DATE_TIME => self::OPTION_GROUP_DATE,
            self::OPTION_TYPE_TIME => self::OPTION_GROUP_DATE,
        ];

        return isset($optionGroupsToTypes[$type]) ? $optionGroupsToTypes[$type] : '';
    }

    /**
     * Group model factory
     *
     * @param string $type Option type
     * @return \Magento\Catalog\Model\Product\Option\Type\DefaultType
     * @throws Exception
     */
    public function groupFactory($type)
    {
        $group = $this->getGroupByType($type);
        if (!empty($group)) {
            return $this->_optionFactory->create(
                'Magento\Catalog\Model\Product\Option\Type\\' . $this->string->upperCaseWords($group)
            );
        }
        throw new Exception(__('The option type to get group instance is incorrect.'));
    }

    /**
     * Save options.
     *
     * @return $this
     */
    public function saveOptions()
    {
        foreach ($this->getOptions() as $option) {
            $this->_validatorBeforeSave = null;
            $this->setData(
                $option
            )->setData(
                'product_id',
                $this->getProduct()->getId()
            )->setData(
                'store_id',
                $this->getProduct()->getStoreId()
            );
            /** Reset is delete flag from the previous iteration */
            $this->isDeleted(false);

            if ($this->getData('option_id') == '0') {
                $this->unsetData('option_id');
            } else {
                $this->setId($this->getData('option_id'));
            }
            $isEdit = (bool)$this->getId() ? true : false;

            if ($this->getData('is_delete') == '1') {
                if ($isEdit) {
                    $this->getValueInstance()->deleteValue($this->getId());
                    $this->deletePrices($this->getId());
                    $this->deleteTitles($this->getId());
                    $this->delete();
                }
            } else {
                if ($this->getData('previous_type') != '') {
                    $previousType = $this->getData('previous_type');

                    /**
                     * if previous option has different group from one is came now
                     * need to remove all data of previous group
                     */
                    if ($this->getGroupByType($previousType) != $this->getGroupByType($this->getData('type'))) {
                        switch ($this->getGroupByType($previousType)) {
                            case self::OPTION_GROUP_SELECT:
                                $this->unsetData('values');
                                if ($isEdit) {
                                    $this->getValueInstance()->deleteValue($this->getId());
                                }
                                break;
                            case self::OPTION_GROUP_FILE:
                                $this->setData('file_extension', '');
                                $this->setData('image_size_x', '0');
                                $this->setData('image_size_y', '0');
                                break;
                            case self::OPTION_GROUP_TEXT:
                                $this->setData('max_characters', '0');
                                break;
                            case self::OPTION_GROUP_DATE:
                                break;
                        }
                        if ($this->getGroupByType($this->getData('type')) == self::OPTION_GROUP_SELECT) {
                            $this->setData('sku', '');
                            $this->unsetData('price');
                            $this->unsetData('price_type');
                            if ($isEdit) {
                                $this->deletePrices($this->getId());
                            }
                        }
                    }
                }
                $this->save();
            }
        }
        //eof foreach()
        return $this;
    }

    /**
     * @return AbstractModel
     * @throws \Magento\Framework\Model\Exception
     */
    public function afterSave()
    {
        $this->getValueInstance()->unsetValues();
        if (is_array($this->getData('values'))) {
            foreach ($this->getData('values') as $value) {
                $this->getValueInstance()->addValue($value);
            }

            $this->getValueInstance()->setOption($this)->saveValues();
        } elseif ($this->getGroupByType($this->getType()) == self::OPTION_GROUP_SELECT) {
            throw new Exception(__('Select type options required values rows.'));
        }

        return parent::afterSave();
    }

    /**
     * Return price. If $flag is true and price is percent
     *  return converted percent to price
     *
     * @param bool $flag
     * @return float
     */
    public function getPrice($flag = false)
    {
        if ($flag && $this->getPriceType() == 'percent') {
            $basePrice = $this->getProduct()->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();
            $price = $basePrice * ($this->_getData('price') / 100);
            return $price;
        }
        return $this->_getData('price');
    }

    /**
     * Delete prices of option
     *
     * @param int $option_id
     * @return $this
     */
    public function deletePrices($option_id)
    {
        $this->getResource()->deletePrices($option_id);
        return $this;
    }

    /**
     * Delete titles of option
     *
     * @param int $option_id
     * @return $this
     */
    public function deleteTitles($option_id)
    {
        $this->getResource()->deleteTitles($option_id);
        return $this;
    }

    /**
     * Get Product Option Collection
     *
     * @param Product $product
     * @return \Magento\Catalog\Model\Resource\Product\Option\Collection
     */
    public function getProductOptionCollection(Product $product)
    {
        $collection = $this->getCollection()->addFieldToFilter(
            'product_id',
            $product->getId()
        )->addTitleToResult(
            $product->getStoreId()
        )->addPriceToResult(
            $product->getStoreId()
        )->setOrder(
            'sort_order',
            'asc'
        )->setOrder(
            'title',
            'asc'
        );

        if ($this->getAddRequiredFilter()) {
            $collection->addRequiredFilter($this->getAddRequiredFilterValue());
        }

        $collection->addValuesToResult($product->getStoreId());
        return $collection;
    }

    /**
     * Get collection of values for current option
     *
     * @return Collection
     */
    public function getValuesCollection()
    {
        $collection = $this->getValueInstance()->getValuesCollection($this);

        return $collection;
    }

    /**
     * Get collection of values by given option ids
     *
     * @param array $optionIds
     * @param int $store_id
     * @return Collection
     */
    public function getOptionValuesByOptionId($optionIds, $store_id)
    {
        $collection = $this->_productOptionValue->getValuesByOption($optionIds, $this->getId(), $store_id);

        return $collection;
    }

    /**
     * Duplicate options for product
     *
     * @param int $oldProductId
     * @param int $newProductId
     * @return $this
     */
    public function duplicate($oldProductId, $newProductId)
    {
        $this->getResource()->duplicate($this, $oldProductId, $newProductId);

        return $this;
    }

    /**
     * Retrieve option searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        return $this->_getResource()->getSearchableData($productId, $storeId);
    }

    /**
     * Clearing object's data
     *
     * @return $this
     */
    protected function _clearData()
    {
        $this->_data = [];
        $this->_values = [];
        return $this;
    }

    /**
     * Clearing cyclic references
     *
     * @return $this
     */
    protected function _clearReferences()
    {
        if (!empty($this->_values)) {
            foreach ($this->_values as $value) {
                $value->unsetOption();
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validatorPool->get($this->getType());
    }

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku()
    {
        $productSku = $this->_getData('product_sku');
        if (!$productSku) {
            $productSku = $this->getProduct()->getSku();
        }
        return $productSku;
    }

    /**
     * Get option id
     *
     * @return int|null
     * @codeCoverageIgnoreStart
     */
    public function getOptionId()
    {
        return $this->_getData('option_id');
    }

    /**
     * Get option title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }

    /**
     * Get option type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_getData('type');
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_getData('sort_order');
    }

    /**
     * Get is require
     *
     * @return bool
     */
    public function getIsRequire()
    {
        return $this->_getData('is_require');
    }

    /**
     * Get price type
     *
     * @return string|null
     */
    public function getPriceType()
    {
        return $this->_getData('price_type');
    }

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->_getData('sku');
    }

    /**
     * @return string|null
     */
    public function getFileExtension()
    {
        return $this->getData('file_extension');
    }

    /**
     * @return int|null
     */
    public function getMaxCharacters()
    {
        return $this->getData('max_characters');
    }

    /**
     * @return int|null
     */
    public function getImageSizeX()
    {
        return $this->getData('image_size_x');
    }

    /**
     * @return int|null
     */
    public function getImageSizeY()
    {
        return $this->getData('image_size_y');
    }
    //@codeCoverageIgnoreEnd
}
