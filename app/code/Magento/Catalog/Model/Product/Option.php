<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Type\Date;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Model\Product\Option\Type\File;
use Magento\Catalog\Model\Product\Option\Type\Select;
use Magento\Catalog\Model\Product\Option\Type\Text;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Catalog product option model
 *
 * @api
 * @method int getProductId()
 * @method \Magento\Catalog\Model\Product\Option setProductId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 100.0.2
 */
class Option extends AbstractExtensibleModel implements ProductCustomOptionInterface
{
    /**
     * @var Option\Repository
     * @since 101.0.0
     */
    protected $optionRepository;

    /**
     * Option type percent
     * @since 101.0.0
     */
    protected static $typePercent = 'percent';

    /**#@+
     * Constants
     */
    const KEY_PRODUCT_SKU = 'product_sku';
    const KEY_OPTION_ID = 'option_id';
    const KEY_TITLE = 'title';
    const KEY_TYPE = 'type';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_IS_REQUIRE = 'is_require';
    const KEY_PRICE = 'price';
    const KEY_PRICE_TYPE = 'price_type';
    const KEY_SKU = 'sku';
    const KEY_FILE_EXTENSION = 'file_extension';
    const KEY_MAX_CHARACTERS = 'max_characters';
    const KEY_IMAGE_SIZE_Y = 'image_size_y';
    const KEY_IMAGE_SIZE_X = 'image_size_x';
    /**#@-*/

    /**#@-*/
    protected $product;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $values = null;

    /**
     * Catalog product option value
     *
     * @var Option\Value
     */
    protected $productOptionValue;

    /**
     * Product option factory
     *
     * @var \Magento\Catalog\Model\Product\Option\Type\Factory
     */
    protected $optionTypeFactory;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var Option\Validator\Pool
     */
    protected $validatorPool;

    /**
     * @var string[]
     */
    private $optionGroups;

    /**
     * @var string[]
     */
    private $optionTypesToGroups;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ProductCustomOptionValuesInterfaceFactory
     */
    private $customOptionValuesFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param Option\Value $productOptionValue
     * @param Option\Type\Factory $optionFactory
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param Option\Validator\Pool $validatorPool
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param ProductCustomOptionValuesInterfaceFactory|null $customOptionValuesFactory
     * @param array $optionGroups
     * @param array $optionTypesToGroups
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        Option\Value $productOptionValue,
        \Magento\Catalog\Model\Product\Option\Type\Factory $optionFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        Option\Validator\Pool $validatorPool,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ProductCustomOptionValuesInterfaceFactory $customOptionValuesFactory = null,
        array $optionGroups = [],
        array $optionTypesToGroups = []
    ) {
        $this->productOptionValue = $productOptionValue;
        $this->optionTypeFactory = $optionFactory;
        $this->string = $string;
        $this->validatorPool = $validatorPool;
        $this->customOptionValuesFactory = $customOptionValuesFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(ProductCustomOptionValuesInterfaceFactory::class);
        $this->optionGroups = $optionGroups ?: [
            self::OPTION_GROUP_DATE => Date::class,
            self::OPTION_GROUP_FILE => File::class,
            self::OPTION_GROUP_SELECT => Select::class,
            self::OPTION_GROUP_TEXT => Text::class,
        ];
        $this->optionTypesToGroups = $optionTypesToGroups ?: [
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

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @deprecated 102.0.0 because resource models should be used directly
     */
    protected function _getResource()
    {
        return $this->_resource ?: parent::_getResource();
    }

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Product\Option::class);
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
        $this->values[$value->getId()] = $value;
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
        if (isset($this->values[$valueId])) {
            return $this->values[$valueId];
        }

        return null;
    }

    /**
     * Whether or not the option type contains sub-values
     *
     * @param string $type
     * @return bool
     * @since 102.0.0
     */
    public function hasValues($type = null)
    {
        return $this->getGroupByType($type) == self::OPTION_GROUP_SELECT;
    }

    /**
     * Get values
     *
     * @return ProductCustomOptionValuesInterface[]|null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Retrieve value instance
     *
     * @return Option\Value
     */
    public function getValueInstance()
    {
        return $this->productOptionValue;
    }

    /**
     * Add option for save it
     *
     * @param array $option
     * @return $this
     */
    public function addOption($option)
    {
        $this->options[] = $option;
        return $this;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options for array
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Set options to empty array
     *
     * @return $this
     */
    public function unsetOptions()
    {
        $this->options = [];
        return $this;
    }

    /**
     * Retrieve product instance
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set product instance
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product = null)
    {
        $this->product = $product;
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
        if ($type === null) {
            $type = $this->getType();
        }

        return $this->optionTypesToGroups[$type] ?? '';
    }

    /**
     * Group model factory
     *
     * @param string $type Option type
     * @return DefaultType
     * @throws LocalizedException
     */
    public function groupFactory($type)
    {
        $group = $this->getGroupByType($type);
        if (!empty($group) && isset($this->optionGroups[$group])) {
            return $this->optionTypeFactory->create($this->optionGroups[$group]);
        }
        throw new LocalizedException(__('The option type to get group instance is incorrect.'));
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 101.0.0
     */
    public function beforeSave()
    {
        parent::beforeSave();
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
                        if ($this->getId()) {
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
                    if ($this->getId()) {
                        $this->deletePrices($this->getId());
                    }
                }
            }
        }
        if ($this->getGroupByType($this->getData('type')) === self::OPTION_GROUP_FILE) {
            $this->cleanFileExtensions();
        }

        return $this;
    }

    /**
     * After save
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        $values = $this->getValues() ?: $this->getData('values');
        if (is_array($values)) {
            foreach ($values as $value) {
                if ($value instanceof ProductCustomOptionValuesInterface) {
                    $data = $value->getData();
                } else {
                    $data = $value;
                }

                $this->customOptionValuesFactory->create()
                    ->addValue($data)
                    ->setOption($this)
                    ->saveValues();
            }
        } elseif ($this->getGroupByType($this->getType()) === self::OPTION_GROUP_SELECT) {
            throw new LocalizedException(__('Select type options required values rows.'));
        }

        return parent::afterSave();
    }

    /**
     * Return price. If $flag is true and price is percent
     *
     * Return converted percent to price
     *
     * @param bool $flag
     * @return float
     */
    public function getPrice($flag = false)
    {
        if ($flag && $this->getPriceType() == self::$typePercent) {
            $basePrice = $this->getProduct()->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();
            $price = $basePrice * ($this->_getData(self::KEY_PRICE) / 100);
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }

    /**
     * Delete prices of option
     *
     * @param int $optionId
     * @return $this
     */
    public function deletePrices($optionId)
    {
        $this->getResource()->deletePrices($optionId);
        return $this;
    }

    /**
     * Delete titles of option
     *
     * @param int $optionId
     * @return $this
     */
    public function deleteTitles($optionId)
    {
        $this->getResource()->deleteTitles($optionId);
        return $this;
    }

    /**
     * Get Product Option Collection
     *
     * @param Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
     * @since 101.0.0
     */
    public function getProductOptions(Product $product)
    {
        return $this->getOptionRepository()->getProductOptions($product, $this->getAddRequiredFilter());
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
     * @param int $storeId
     * @return Collection
     */
    public function getOptionValuesByOptionId($optionIds, $storeId)
    {
        $collection = $this->productOptionValue->getValuesByOption($optionIds, $this->getId(), $storeId);

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
        $this->values = null;
        return $this;
    }

    /**
     * Clearing cyclic references
     *
     * @return $this
     */
    protected function _clearReferences()
    {
        if (!empty($this->values)) {
            foreach ($this->values as $value) {
                $value->unsetOption();
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
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
        $productSku = $this->_getData(self::KEY_PRODUCT_SKU);
        if (!$productSku && $this->getProduct()) {
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
        return $this->_getData(self::KEY_OPTION_ID);
    }

    /**
     * Get option title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData(self::KEY_TITLE);
    }

    /**
     * Get option type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_getData(self::KEY_TYPE);
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_getData(self::KEY_SORT_ORDER);
    }

    /**
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRequire()
    {
        return $this->_getData(self::KEY_IS_REQUIRE);
    }

    /**
     * Get price type
     *
     * @return string|null
     */
    public function getPriceType()
    {
        return $this->_getData(self::KEY_PRICE_TYPE);
    }

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->_getData(self::KEY_SKU);
    }

    /**
     * Get file extension
     *
     * @return string|null
     */
    public function getFileExtension()
    {
        return $this->getData(self::KEY_FILE_EXTENSION);
    }

    /**
     * Get Max Characters
     *
     * @return int|null
     */
    public function getMaxCharacters()
    {
        return $this->getData(self::KEY_MAX_CHARACTERS);
    }

    /**
     * Get image size X
     *
     * @return int|null
     */
    public function getImageSizeX()
    {
        return $this->getData(self::KEY_IMAGE_SIZE_X);
    }

    /**
     * Get image size Y
     *
     * @return int|null
     */
    public function getImageSizeY()
    {
        return $this->getData(self::KEY_IMAGE_SIZE_Y);
    }

    /**
     * Set product SKU
     *
     * @param string $productSku
     * @return $this
     */
    public function setProductSku($productSku)
    {
        return $this->setData(self::KEY_PRODUCT_SKU, $productSku);
    }

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::KEY_OPTION_ID, $optionId);
    }

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * Set option type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequire($isRequired)
    {
        return $this->setData(self::KEY_IS_REQUIRE, $isRequired);
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType)
    {
        return $this->setData(self::KEY_PRICE_TYPE, $priceType);
    }

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * Set File Extension
     *
     * @param string $fileExtension
     * @return $this
     */
    public function setFileExtension($fileExtension)
    {
        return $this->setData(self::KEY_FILE_EXTENSION, $fileExtension);
    }

    /**
     * Set Max Characters
     *
     * @param int $maxCharacters
     * @return $this
     */
    public function setMaxCharacters($maxCharacters)
    {
        return $this->setData(self::KEY_MAX_CHARACTERS, $maxCharacters);
    }

    /**
     * Set Image Size X
     *
     * @param int $imageSizeX
     * @return $this
     */
    public function setImageSizeX($imageSizeX)
    {
        return $this->setData(self::KEY_IMAGE_SIZE_X, $imageSizeX);
    }

    /**
     * Set Image Size Y
     *
     * @param int $imageSizeY
     * @return $this
     */
    public function setImageSizeY($imageSizeY)
    {
        return $this->setData(self::KEY_IMAGE_SIZE_Y, $imageSizeY);
    }

    /**
     * Set value
     *
     * @param ProductCustomOptionValuesInterface[] $values
     * @return $this
     */
    public function setValues(array $values = null)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Return regular price.
     *
     * @return float|int
     * @since 101.0.0
     */
    public function getRegularPrice()
    {
        if ($this->getPriceType() == self::$typePercent) {
            $basePrice = $this->getProduct()->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
            $price = $basePrice * ($this->_getData(self::KEY_PRICE) / 100);
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }

    /**
     * Get Product Option Collection
     *
     * @param Product $product
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getProductOptionCollection(Product $product)
    {
        $collection = clone $this->getCollection();
        $collection->addFieldToFilter(
            'product_id',
            $product->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField())
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
     * @inheritdoc
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get option repository
     *
     * @return Option\Repository
     */
    private function getOptionRepository()
    {
        if (null === $this->optionRepository) {
            $this->optionRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\Product\Option\Repository::class);
        }
        return $this->optionRepository;
    }

    /**
     * Get metadata pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }

    //@codeCoverageIgnoreEnd

    /**
     * Clears all non-accepted characters from file_extension field.
     *
     * @return void
     */
    private function cleanFileExtensions()
    {
        $rawExtensions = $this->getFileExtension();
        $matches = [];
        preg_match_all('/(?<extensions>[a-z0-9]+)/i', strtolower($rawExtensions), $matches);
        if (!empty($matches)) {
            $extensions = implode(', ', array_unique($matches['extensions']));
            $this->setFileExtension($extensions);
        }
    }
}
