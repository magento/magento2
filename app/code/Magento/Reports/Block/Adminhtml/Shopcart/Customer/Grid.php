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
namespace Magento\Reports\Block\Adminhtml\Shopcart\Customer;

/**
 * Adminhtml items in carts report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\Shopcart
{
    /**
     * @var \Magento\Reports\Model\Resource\Customer\CollectionFactory
     */
    protected $_customersFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\Resource\Customer\CollectionFactory $customersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\Resource\Customer\CollectionFactory $customersFactory,
        array $data = array()
    ) {
        $this->_customersFactory = $customersFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        //TODO: add full name logic
        $collection = $this->_customersFactory->create()->addAttributeToSelect(
            'firstname'
        )->addAttributeToSelect(
            'lastname'
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid|void
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addCartInfo();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array('header' => __('ID'), 'width' => '50px', 'align' => 'right', 'index' => 'entity_id')
        );

        $this->addColumn('firstname', array('header' => __('First Name'), 'index' => 'firstname'));

        $this->addColumn('lastname', array('header' => __('Last Name'), 'index' => 'lastname'));

        $this->addColumn(
            'items',
            array(
                'header' => __('Items in Cart'),
                'width' => '70px',
                'sortable' => false,
                'align' => 'right',
                'index' => 'items'
            )
        );

        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn(
            'total',
            array(
                'header' => __('Total'),
                'width' => '70px',
                'sortable' => false,
                'type' => 'currency',
                'align' => 'right',
                'currency_code' => $currencyCode,
                'index' => 'total',
                'renderer' => 'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency',
                'rate' => $this->getRate($currencyCode)
            )
        );

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportCustomerCsv', __('CSV'));
        $this->addExportType('*/*/exportCustomerExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
