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
namespace Magento\Backend\Block\Dashboard\Tab\Products;

/**
 * Adminhtml dashboard most ordered products grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ordered extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Sales\Model\Resource\Report\Bestsellers\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Sales\Model\Resource\Report\Bestsellers\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Sales\Model\Resource\Report\Bestsellers\CollectionFactory $collectionFactory,
        array $data = array()
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsOrderedGrid');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        if (!$this->_moduleManager->isEnabled('Magento_Sales')) {
            return $this;
        }
        if ($this->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else {
            $storeId = (int)$this->getParam('store');
        }

        $collection = $this->_collectionFactory->create()->setModel(
            'Magento\Catalog\Model\Product'
        )->addStoreFilter(
            $storeId
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {

        $this->addColumn('name', array('header' => __('Product'), 'sortable' => false, 'index' => 'product_name'));

        $this->addColumn(
            'price',
            array(
                'header' => __('Price'),
                'width' => '120px',
                'type' => 'currency',
                'currency_code' => (string)$this->_storeManager->getStore(
                    (int)$this->getParam('store')
                )->getBaseCurrencyCode(),
                'sortable' => false,
                'index' => 'product_price'
            )
        );

        $this->addColumn(
            'ordered_qty',
            array(
                'header' => __('Order Quantity'),
                'width' => '120px',
                'align' => 'right',
                'sortable' => false,
                'index' => 'qty_ordered',
                'type' => 'number'
            )
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * Returns row url to show in admin dashboard
     * $row is bestseller row wrapped in Product model
     *
     * @param \Magento\Catalog\Model\Product $row
     * @return string
     */
    public function getRowUrl($row)
    {
        // getId() would return id of bestseller row, and product id we get by getProductId()
        $productId = $row->getProductId();

        // No url is possible for non-existing products
        if (!$productId) {
            return '';
        }

        $params = array('id' => $productId);
        if ($this->getRequest()->getParam('store')) {
            $params['store'] = $this->getRequest()->getParam('store');
        }
        return $this->getUrl('catalog/product/edit', $params);
    }
}
