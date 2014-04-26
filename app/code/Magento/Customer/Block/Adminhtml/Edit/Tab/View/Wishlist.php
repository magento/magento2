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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml customer view wishlist block
 */
class Wishlist extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Wishlist item collection factory.
     *
     * @var \Magento\Wishlist\Model\Resource\Item\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Wishlist\Model\Resource\Item\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Wishlist\Model\Resource\Item\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initial settings.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_view_wishlist_grid');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setEmptyText(__("There are no items in customer's wishlist at the moment"));
    }

    /**
     * Prepare collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create()->addCustomerIdFilter(
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        )->addDaysInWishlist()->addStoreData()->setInStockFilter(
            true
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns.
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            array('header' => __('ID'), 'index' => 'product_id', 'type' => 'number', 'width' => '100px')
        );

        $this->addColumn(
            'product_name',
            array(
                'header' => __('Product'),
                'index' => 'product_name',
                'renderer' => 'Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item'
            )
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store',
                array('header' => __('Add Locale'), 'index' => 'store_id', 'type' => 'store', 'width' => '160px')
            );
        }

        $this->addColumn(
            'added_at',
            array('header' => __('Add Date'), 'index' => 'added_at', 'type' => 'date', 'width' => '140px')
        );

        $this->addColumn(
            'days',
            array(
                'header' => __('Days in Wish List'),
                'index' => 'days_in_wishlist',
                'type' => 'number',
                'width' => '140px'
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Get headers visibility
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', array('id' => $row->getProductId()));
    }
}
