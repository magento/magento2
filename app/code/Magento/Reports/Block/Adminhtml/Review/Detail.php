<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Review;

/**
 * Adminhtml report review product blocks content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Detail extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $_productFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_review_detail';

        $product = $this->_productFactory->create()->load($this->getRequest()->getParam('id'));
        $this->_headerText = __('Reviews for %1', $product->getName());

        parent::_construct();
        $this->buttonList->remove('add');
        $this->setBackUrl($this->getUrl('reports/report_review/product/'));
        $this->_addBackButton();
    }
}
