<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customers defined options
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options;

use Magento\Backend\Block\Widget;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Store\Model\Store;

/**
 * Block for rendering option of product
 *
 * Class \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Option extends Widget
{
    /**
     * @var Product
     */
    protected $_productInstance;

    /**
     * @var \Magento\Framework\DataObject[]
     */
    protected $_values;

    /**
     * @var int
     */
    protected $_itemCount = 1;

    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/edit/options/option.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $_productOptionConfig;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_configYesNo;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Type
     */
    protected $_optionType;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $configYesNo
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Type $optionType
     * @param Product $product
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $configYesNo,
        \Magento\Catalog\Model\Config\Source\Product\Options\Type $optionType,
        Product $product,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        array $data = []
    ) {
        $this->_optionType = $optionType;
        $this->_configYesNo = $configYesNo;
        $this->_product = $product;
        $this->_productOptionConfig = $productOptionConfig;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * Get Item Count
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->_itemCount;
    }

    /**
     * Set Item Count
     *
     * @param int $itemCount
     * @return $this
     */
    public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }

    /**
     * Get Product
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_productInstance) {
            $product = $this->_coreRegistry->registry('product');
            if ($product) {
                $this->_productInstance = $product;
            } else {
                $this->_productInstance = $this->_product;
            }
        }

        return $this->_productInstance;
    }

    /**
     * Set Product
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_productInstance = $product;
        return $this;
    }

    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'product[options]';
    }

    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'product_option';
    }

    /**
     * Check block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getProduct()->getOptionsReadonly();
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        foreach ($this->_productOptionConfig->getAll() as $option) {
            $this->addChild($option['name'] . '_option_type', $option['renderer']);
        }

        return parent::_prepareLayout();
    }

    /**
     * Get Add Button Id
     *
     * @return mixed
     */
    public function getAddButtonId()
    {
        $buttonId = $this->getLayout()->getBlock('admin.product.options')->getChildBlock('add_button')->getId();
        return $buttonId;
    }

    /**
     * Get Type Select Html
     *
     * @return mixed
     */
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_type',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][type]'
        )->setOptions(
            $this->_optionType->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Get Require Select Html
     *
     * @return mixed
     */
    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            ['id' => $this->getFieldId() . '_<%- data.id %>_is_require', 'class' => 'select']
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][is_require]'
        )->setOptions(
            $this->_configYesNo->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Retrieve html templates for different types of product custom options
     *
     * @return string
     */
    public function getTemplatesHtml()
    {
        $canEditPrice = $this->getCanEditPrice();
        $canReadPrice = $this->getCanReadPrice();
        $this->getChildBlock('select_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $this->getChildBlock('file_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $this->getChildBlock('date_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $this->getChildBlock('text_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $templates = $this->getChildHtml(
            'text_option_type'
        ) . "\n" . $this->getChildHtml(
            'file_option_type'
        ) . "\n" . $this->getChildHtml(
            'select_option_type'
        ) . "\n" . $this->getChildHtml(
            'date_option_type'
        );

        return $templates;
    }

    /**
     * Get Option Values
     *
     * @return \Magento\Framework\DataObject[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOptionValues()
    {
        $optionsArr = $this->getProduct()->getOptions();
        if ($optionsArr == null) {
            $optionsArr = [];
        }

        if (!$this->_values || $this->getIgnoreCaching()) {
            $showPrice = $this->getCanReadPrice();
            $values = [];
            $scope = (int)$this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            foreach ($optionsArr as $option) {
                /* @var $option \Magento\Catalog\Model\Product\Option */

                $this->setItemCount($option->getOptionId());

                $value = [];

                $value['id'] = $option->getOptionId();
                $value['item_count'] = $this->getItemCount();
                $value['option_id'] = $option->getOptionId();
                $value['title'] = $option->getTitle();
                $value['type'] = $option->getType();
                $value['is_require'] = $option->getIsRequire();
                $value['sort_order'] = $option->getSortOrder();
                $value['can_edit_price'] = $this->getCanEditPrice();

                if ($this->getProduct()->getStoreId() != Store::DEFAULT_STORE_ID) {
                    $value['checkboxScopeTitle'] = $this->getCheckboxScopeHtml(
                        $option->getOptionId(),
                        'title',
                        $option->getStoreTitle() === null
                    );
                    $value['scopeTitleDisabled'] = $option->getStoreTitle() === null ? 'disabled' : null;
                }

                if ($option->getGroupByType() == ProductCustomOptionInterface::OPTION_GROUP_SELECT) {
                    $value = $this->getOptionValueOfGroupSelect($value, $option, $showPrice, $scope);
                } else {
                    $value['price'] = $showPrice ? $this->getPriceValue(
                        $option->getPrice(),
                        $option->getPriceType()
                    ) : '';
                    $value['price_type'] = $option->getPriceType();
                    $value['sku'] = $option->getSku();
                    $value['max_characters'] = $option->getMaxCharacters();
                    $value['file_extension'] = $option->getFileExtension();
                    $value['image_size_x'] = $option->getImageSizeX();
                    $value['image_size_y'] = $option->getImageSizeY();
                    if ($this->getProduct()->getStoreId() != Store::DEFAULT_STORE_ID
                        && $scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE
                    ) {
                        $value['checkboxScopePrice'] = $this->getCheckboxScopeHtml(
                            $option->getOptionId(),
                            'price',
                            $option->getStorePrice() === null
                        );
                        $value['scopePriceDisabled'] = $option->getStorePrice() === null ? 'disabled' : null;
                    }
                }
                $values[] = new \Magento\Framework\DataObject($value);
            }
            $this->_values = $values;
        }

        return $this->_values;
    }

    /**
     * Get Option Value Of Group Select
     *
     * @param array $value
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param boolean $showPrice
     * @param int $scope
     * @return array
     */
    private function getOptionValueOfGroupSelect($value, $option, $showPrice, $scope)
    {
        $i = 0;
        $itemCount = 0;
        foreach ($option->getValues() as $_value) {
            /* @var $_value \Magento\Catalog\Model\Product\Option\Value */
            $value['optionValues'][$i] = [
                'item_count' => max($itemCount, $_value->getOptionTypeId()),
                'option_id' => $_value->getOptionId(),
                'option_type_id' => $_value->getOptionTypeId(),
                'title' => $_value->getTitle(),
                'price' => $showPrice ? $this->getPriceValue(
                    $_value->getPrice(),
                    $_value->getPriceType()
                ) : '',
                'price_type' => $showPrice ? $_value->getPriceType() : 0,
                'sku' => $_value->getSku(),
                'sort_order' => $_value->getSortOrder(),
            ];

            if ($this->getProduct()->getStoreId() != Store::DEFAULT_STORE_ID) {
                $value['optionValues'][$i]['checkboxScopeTitle'] = $this->getCheckboxScopeHtml(
                    $_value->getOptionId(),
                    'title',
                    $_value->getStoreTitle() === null,
                    $_value->getOptionTypeId()
                );
                $value['optionValues'][$i]['scopeTitleDisabled'] = $_value->getStoreTitle() === null
                    ? 'disabled'
                    : null;
                if ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE) {
                    $value['optionValues'][$i]['checkboxScopePrice'] = $this->getCheckboxScopeHtml(
                        $_value->getOptionId(),
                        'price',
                        $_value->getstorePrice() === null,
                        $_value->getOptionTypeId(),
                        ['$(this).up(1).previous()']
                    );
                    $value['optionValues'][$i]['scopePriceDisabled'] = $_value->getStorePrice() === null
                        ? 'disabled'
                        : null;
                }
            }
            $i++;
        }
        return $value;
    }

    /**
     * Retrieve html of scope checkbox
     *
     * @param string $id
     * @param string $name
     * @param boolean $checked
     * @param string $select_id
     * @param array $containers
     * @return string
     */
    public function getCheckboxScopeHtml($id, $name, $checked = true, $select_id = '-1', array $containers = [])
    {
        $checkedHtml = '';
        if ($checked) {
            $checkedHtml = ' checked="checked"';
        }
        $selectNameHtml = '';
        $selectIdHtml = '';
        if ($select_id != '-1') {
            $selectNameHtml = '[values][' . $select_id . ']';
            $selectIdHtml = 'select_' . $select_id . '_';
        }
        $containers[] = '$(this).up(1)';
        $containers = implode(',', $containers);
        $localId = $this->getFieldId() . '_' . $id . '_' . $selectIdHtml . $name . '_use_default';
        $localName = "options_use_default[" . $id . "]" . $selectNameHtml . "[" . $name . "]";
        $useDefault =
            '<div class="field-service">'
            . '<input type="checkbox" class="use-default-control"'
            . ' name="' . $localName . '"' . 'id="' . $localId . '"'
            . ' value=""'
            . $checkedHtml
            . ' onchange="toggleSeveralValueElements(this, [' . $containers . ']);" '
            . ' />'
            . '<label for="' . $localId . '" class="use-default">'
            . '<span class="use-default-label">' . __('Use Default') . '</span></label></div>';

        return $useDefault;
    }

    /**
     * Get Price Value
     *
     * @param float $value
     * @param string $type
     * @return string
     */
    public function getPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format((float)$value, 2, null, '');
        } elseif ($type == 'fixed') {
            return number_format((float)$value, 2, null, '');
        }
    }

    /**
     * Return product grid url for custom options import popup
     *
     * @return string
     */
    public function getProductGridUrl()
    {
        return $this->getUrl('catalog/*/optionsImportGrid');
    }

    /**
     * Return custom options getter URL for ajax queries
     *
     * @return string
     */
    public function getCustomOptionsUrl()
    {
        return $this->getUrl('catalog/*/customOptions');
    }

    /**
     * Return current product id
     *
     * @return null|int
     */
    public function getCurrentProductId()
    {
        return $this->getProduct()->getId();
    }
}
