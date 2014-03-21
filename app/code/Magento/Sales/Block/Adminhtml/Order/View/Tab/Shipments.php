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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order Shipments grid
 */
class Shipments extends \Magento\Backend\Block\Widget\Grid\Extended implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Collection factory
     *
     * @var \Magento\Sales\Model\Resource\Order\Collection\Factory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\Resource\Order\Collection\Factory $collectionFactory
     * @param \Magento\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\Resource\Order\Collection\Factory $collectionFactory,
        \Magento\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_shipments');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'Magento\Sales\Model\Resource\Order\Shipment\Grid\Collection';
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create(
            $this->_getCollectionClass()
        )->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'increment_id'
        )->addFieldToSelect(
            'total_qty'
        )->addFieldToSelect(
            'shipping_name'
        )->setOrderFilter(
            $this->getOrder()
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Initialize grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            array(
                'header' => __('Shipment'),
                'index' => 'increment_id',
                'header_css_class' => 'col-memo',
                'column_css_class' => 'col-memo'
            )
        );

        $this->addColumn(
            'shipping_name',
            array(
                'header' => __('Ship-to Name'),
                'index' => 'shipping_name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => __('Ship Date'),
                'index' => 'created_at',
                'type' => 'datetime',
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            )
        );

        $this->addColumn(
            'total_qty',
            array(
                'header' => __('Total Quantity'),
                'index' => 'total_qty',
                'type' => 'number',
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Row URL getter
     *
     * @param \Magento\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'adminhtml/order_shipment/view',
            array('shipment_id' => $row->getId(), 'order_id' => $row->getOrderId())
        );
    }

    /**
     * Grid URL getter
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales/*/shipments', array('_current' => true));
    }

    /**
     * ######################## TAB settings #################################
     */

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Shipments');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Shipments');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if ($this->getOrder()->getIsVirtual()) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
