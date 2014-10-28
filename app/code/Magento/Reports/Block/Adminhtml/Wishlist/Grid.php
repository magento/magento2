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
namespace Magento\Reports\Block\Adminhtml\Wishlist;

/**
 * Adminhtml wishlist report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Reports\Model\Resource\Wishlist\Product\CollectionFactory
     */
    protected $_productsFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\Resource\Wishlist\Product\CollectionFactory $productsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\Resource\Wishlist\Product\CollectionFactory $productsFactory,
        array $data = array()
    ) {
        $this->_productsFactory = $productsFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('wishlistReportGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productsFactory->create()->addAttributeToSelect(
            'entity_id'
        )->addAttributeToSelect(
            'name'
        )->addWishlistCount();

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array('header' => __('ID'), 'width' => '50px', 'index' => 'entity_id'));

        $this->addColumn('name', array('header' => __('Name'), 'index' => 'name'));

        $this->addColumn(
            'wishlists',
            array('header' => __('Wish Lists'), 'width' => '50px', 'align' => 'right', 'index' => 'wishlists')
        );

        $this->addColumn(
            'bought_from_wishlists',
            array(
                'header' => __('Wishlist Purchase'),
                'width' => '50px',
                'align' => 'right',
                'sortable' => false,
                'index' => 'bought_from_wishlists'
            )
        );

        $this->addColumn(
            'w_vs_order',
            array(
                'header' => __('Wish List vs. Regular Order'),
                'width' => '50px',
                'align' => 'right',
                'sortable' => false,
                'index' => 'w_vs_order'
            )
        );

        $this->addColumn(
            'num_deleted',
            array(
                'header' => __('Times Deleted'),
                'width' => '50px',
                'align' => 'right',
                'sortable' => false,
                'index' => 'num_deleted'
            )
        );

        $this->addExportType('*/*/exportWishlistCsv', __('CSV'));
        $this->addExportType('*/*/exportWishlistExcel', __('Excel XML'));

        $this->setFilterVisibility(false);

        return parent::_prepareColumns();
    }
}
