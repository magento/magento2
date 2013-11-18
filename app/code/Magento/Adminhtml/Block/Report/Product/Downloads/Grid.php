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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml product downloads report grid
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Report\Product\Downloads;

class Grid extends \Magento\Adminhtml\Block\Widget\Grid
{
    /**
     * @var \Magento\Reports\Model\Resource\Product\Downloads\CollectionFactory
     */
    protected $_downloadsFactory;

    /**
     * @param \Magento\Reports\Model\Resource\Product\Downloads\CollectionFactory $downloadsFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Url $urlModel
     * @param array $data
     */
    public function __construct(
        \Magento\Reports\Model\Resource\Product\Downloads\CollectionFactory $downloadsFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Url $urlModel,
        array $data = array()
    ) {
        $this->_downloadsFactory = $downloadsFactory;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('downloadsGrid');
        $this->setUseAjax(false);
    }

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

        $collection = $this->_downloadsFactory->create()
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->addAttributeToFilter('type_id', array(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE))
            ->addSummary();

        if ($storeId) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => __('Product'),
            'index'     => 'name',
            'header_css_class'  => 'col-product',
            'column_css_class'  => 'col-product'
        ));

        $this->addColumn('link_title', array(
            'header'    => __('Link'),
            'index'     => 'link_title',
            'header_css_class'  => 'col-link',
            'column_css_class'  => 'col-link'
        ));

        $this->addColumn('sku', array(
            'header'    =>__('SKU'),
            'index'     =>'sku',
            'header_css_class'  => 'col-sku',
            'column_css_class'  => 'col-sku'
        ));

        $this->addColumn('purchases', array(
            'header'    => __('Purchases'),
            'width'     => '215px',
            'align'     => 'right',
            'filter'    => false,
            'index'     => 'purchases',
            'type'      => 'number',
            'renderer'  => 'Magento\Adminhtml\Block\Report\Product\Downloads\Renderer\Purchases',
            'header_css_class'  => 'col-purchases',
            'column_css_class'  => 'col-purchases'
        ));

        $this->addColumn('downloads', array(
            'header'    => __('Downloads'),
            'width'     => '215px',
            'align'     => 'right',
            'filter'    => false,
            'index'     => 'downloads',
            'type'      => 'number',
            'header_css_class'  => 'col-qty',
            'column_css_class'  => 'col-qty'
        ));

        $this->addExportType('*/*/exportDownloadsCsv', __('CSV'));
        $this->addExportType('*/*/exportDownloadsExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
