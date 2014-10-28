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

/**
 * Adminhtml sales order create search products block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;

class Sku extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogType;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_cpCollection;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $_eavAttSetCollection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_catalogProduct;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $catalogProduct
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $eavAttSetCollection
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $cpCollection
     * @param \Magento\Catalog\Model\Product\Type $catalogType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProduct,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $eavAttSetCollection,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $cpCollection,
        \Magento\Catalog\Model\Product\Type $catalogType,
        array $data = array()
    ) {
        $this->_catalogType = $catalogType;
        $this->_cpCollection = $cpCollection;
        $this->_eavAttSetCollection = $eavAttSetCollection;
        $this->_catalogProduct = $catalogProduct;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('skuChooserGrid_' . $this->getId());
        }

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        $this->setDefaultSort('sku');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $selected = $this->_getSelectedProducts();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('sku', array('in' => $selected));
            } else {
                $this->getCollection()->addFieldToFilter('sku', array('nin' => $selected));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare Catalog Product Collection for attribute SKU in Promo Conditions SKU chooser
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_cpCollection->create()->setStoreId(
            0
        )->addAttributeToSelect(
            'name',
            'type_id',
            'attribute_set_id'
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define Cooser Grid Columns and filters
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'sku',
                'use_index' => true
            )
        );

        $this->addColumn(
            'entity_id',
            array('header' => __('ID'), 'sortable' => true, 'width' => '60px', 'index' => 'entity_id')
        );

        $this->addColumn(
            'type',
            array(
                'header' => __('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_catalogType->getOptionArray()
            )
        );

        $sets = $this->_eavAttSetCollection->create()->setEntityTypeFilter(
            $this->_catalogProduct->create()->getResource()->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'set_name',
            array(
                'header' => __('Attribute Set'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets
            )
        );

        $this->addColumn(
            'chooser_sku',
            array('header' => __('SKU'), 'name' => 'chooser_sku', 'width' => '80px', 'index' => 'sku')
        );
        $this->addColumn(
            'chooser_name',
            array('header' => __('Product'), 'name' => 'chooser_name', 'index' => 'name')
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'catalog_rule/*/chooser',
            array('_current' => true, 'current_grid_id' => $this->getId(), 'collapse' => null)
        );
    }

    /**
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected', array());

        return $products;
    }
}
