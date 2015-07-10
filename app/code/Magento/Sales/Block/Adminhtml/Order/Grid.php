<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Adminhtml sales orders grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponentFactory $componentFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        array $data = []
    ) {
        $this->componentFactory = $componentFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $component = $this->componentFactory->create('sales_order_grid');
        $this->prepareComponent($component);
        $component->render();
        $collection = $component->getContext()->getDataProvider()->getCollection();
        $this->setData('dataSource', $collection);

        return parent::_prepareCollection();
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponentInterface $componentElement
     * @return void
     */
    protected function prepareComponent(\Magento\Framework\View\Element\UiComponentInterface $componentElement)
    {
        foreach ($componentElement->getChildComponents() as $childComponent) {
            $this->prepareComponent($childComponent);
        }
        $componentElement->prepare();
    }
}
