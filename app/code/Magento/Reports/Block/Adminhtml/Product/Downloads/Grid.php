<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Product\Downloads;

/**
 * Adminhtml product downloads report grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\Downloads\CollectionFactory
     */
    protected $_downloadsFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Product\Downloads\CollectionFactory $downloadsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Product\Downloads\CollectionFactory $downloadsFactory,
        array $data = []
    ) {
        $this->_downloadsFactory = $downloadsFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('downloadsGrid');
        $this->setUseAjax(false);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } elseif ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

        $collection = $this->_downloadsFactory->create()->addAttributeToSelect(
            '*'
        )->setStoreId(
            $storeId
        )->addAttributeToFilter(
            'type_id',
            [\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE]
        )->addSummary();

        if ($storeId) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'index' => 'name',
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );

        $this->addColumn(
            'link_title',
            [
                'header' => __('Link'),
                'index' => 'link_title',
                'header_css_class' => 'col-link',
                'column_css_class' => 'col-link'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );

        $this->addColumn(
            'purchases',
            [
                'header' => __('Purchases'),
                'width' => '215px',
                'align' => 'right',
                'filter' => false,
                'index' => 'purchases',
                'type' => 'number',
                'renderer' => \Magento\Reports\Block\Adminhtml\Product\Downloads\Renderer\Purchases::class,
                'header_css_class' => 'col-purchases',
                'column_css_class' => 'col-purchases'
            ]
        );

        $this->addColumn(
            'downloads',
            [
                'header' => __('Downloads'),
                'width' => '215px',
                'align' => 'right',
                'filter' => false,
                'index' => 'downloads',
                'type' => 'number',
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );

        $this->addExportType('*/*/exportDownloadsCsv', __('CSV'));
        $this->addExportType('*/*/exportDownloadsExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
