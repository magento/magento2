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
namespace Magento\Reports\Block\Adminhtml\Product;

/**
 * Adminhtml products report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Reports\Model\Resource\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\Resource\Product\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\Resource\Product\CollectionFactory $collectionFactory,
        array $data = array()
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsReportGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $collection->getEntity()->setStore(0);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return void
     */
    protected function _afterLoadCollection()
    {
        $totalObj = new \Magento\Reports\Model\Totals();
        $this->setTotals($totalObj->countTotals($this));
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array('header' => __('ID'), 'width' => '50px', 'index' => 'entity_id', 'total' => 'Total')
        );

        $this->addColumn('name', array('header' => __('Name'), 'index' => 'name'));

        $this->addColumn(
            'viewed',
            array(
                'header' => __('Viewed'),
                'width' => '50px',
                'align' => 'right',
                'index' => 'viewed',
                'total' => 'sum'
            )
        );

        $this->addColumn(
            'added',
            array('header' => __('Added'), 'width' => '50px', 'align' => 'right', 'index' => 'added', 'total' => 'sum')
        );

        $this->addColumn(
            'purchased',
            array(
                'header' => __('Purchased'),
                'width' => '50px',
                'align' => 'right',
                'index' => 'purchased',
                'total' => 'sum'
            )
        );

        $this->addColumn(
            'fulfilled',
            array(
                'header' => __('Fulfilled'),
                'width' => '50px',
                'align' => 'right',
                'index' => 'fulfilled',
                'total' => 'sum'
            )
        );

        $this->addColumn(
            'revenue',
            array(
                'header' => __('Revenue'),
                'width' => '50px',
                'align' => 'right',
                'index' => 'revenue',
                'total' => 'sum'
            )
        );

        $this->setCountTotals(true);

        $this->addExportType('*/*/exportProductsCsv', __('CSV'));
        $this->addExportType('*/*/exportProductsExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
