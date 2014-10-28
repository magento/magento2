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
namespace Magento\Reports\Block\Adminhtml\Product\Downloads;

/**
 * Adminhtml product downloads report grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Reports\Model\Resource\Product\Downloads\CollectionFactory
     */
    protected $_downloadsFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\Resource\Product\Downloads\CollectionFactory $downloadsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\Resource\Product\Downloads\CollectionFactory $downloadsFactory,
        array $data = array()
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
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
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
            array(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE)
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
            array(
                'header' => __('Product'),
                'index' => 'name',
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            )
        );

        $this->addColumn(
            'link_title',
            array(
                'header' => __('Link'),
                'index' => 'link_title',
                'header_css_class' => 'col-link',
                'column_css_class' => 'col-link'
            )
        );

        $this->addColumn(
            'sku',
            array(
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            )
        );

        $this->addColumn(
            'purchases',
            array(
                'header' => __('Purchases'),
                'width' => '215px',
                'align' => 'right',
                'filter' => false,
                'index' => 'purchases',
                'type' => 'number',
                'renderer' => 'Magento\Reports\Block\Adminhtml\Product\Downloads\Renderer\Purchases',
                'header_css_class' => 'col-purchases',
                'column_css_class' => 'col-purchases'
            )
        );

        $this->addColumn(
            'downloads',
            array(
                'header' => __('Downloads'),
                'width' => '215px',
                'align' => 'right',
                'filter' => false,
                'index' => 'downloads',
                'type' => 'number',
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            )
        );

        $this->addExportType('*/*/exportDownloadsCsv', __('CSV'));
        $this->addExportType('*/*/exportDownloadsExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
