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
namespace Magento\Review\Block\Adminhtml\Product;

/**
 * Adminhtml product grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Catalog\Block\Adminhtml\Product\Grid
{
    /**
     * Website collection
     *
     * @var \Magento\Store\Model\Resource\Website\CollectionFactory
     */
    protected $_websitesFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Store\Model\Resource\Website\CollectionFactory $websitesFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Store\Model\Resource\Website\CollectionFactory $websitesFactory,
        array $data = array()
    ) {
        $this->_websitesFactory = $websitesFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $websiteFactory,
            $setsFactory,
            $productFactory,
            $type,
            $status,
            $visibility,
            $catalogData,
            $data
        );
    }

    /**
     * Initialize review
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setRowClickCallback('review.gridRowClick');
        $this->setUseAjax(true);
    }

    /**
     * Prepare product review grid
     *
     * @return void
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => __('ID'),
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            )
        );

        $this->addColumn('name', array('header' => __('Name'), 'index' => 'name'));

        if ((int)$this->getRequest()->getParam('store', 0)) {
            $this->addColumn('custom_name', array('header' => __('Product Store Name'), 'index' => 'custom_name'));
        }

        $this->addColumn('sku', array('header' => __('SKU'), 'index' => 'sku'));

        $this->addColumn('price', array('header' => __('Price'), 'type' => 'currency', 'index' => 'price'));

        $this->addColumn(
            'qty',
            array('header' => __('Quantity'), 'type' => 'number', 'index' => 'qty')
        );

        $this->addColumn(
            'status',
            array(
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'source' => 'Magento\Catalog\Model\Product\Attribute\Source\Status',
                'options' => $this->_status->getOptionArray()
            )
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'websites',
                array(
                    'header' => __('Websites'),
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => $this->_websitesFactory->create()->toOptionHash()
                )
            );
        }
    }

    /**
     * Get catalog product grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('review/product/productGrid', array('_current' => true));
    }

    /**
     * Get catalog product row url
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('review/product/jsonProductInfo', array('id' => $row->getId()));
    }

    /**
     * Prepare mass action
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }
}
