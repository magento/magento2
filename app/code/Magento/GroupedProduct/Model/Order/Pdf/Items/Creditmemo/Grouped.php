<?php
/**
 * Sales Order Creditmemo Pdf grouped items renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Order\Pdf\Items\Creditmemo;

/**
 * Class \Magento\GroupedProduct\Model\Order\Pdf\Items\Creditmemo\Grouped
 *
 * @since 2.0.0
 */
class Grouped extends \Magento\Sales\Model\Order\Pdf\Items\Creditmemo\DefaultCreditmemo
{
    /**
     * Draw process
     *
     * @return void
     * @since 2.0.0
     */
    public function draw()
    {
        $type = $this->getItem()->getOrderItem()->getRealProductType();
        $renderer = $this->getRenderedModel()->getRenderer($type);
        $renderer->setOrder($this->getOrder());
        $renderer->setItem($this->getItem());
        $renderer->setPdf($this->getPdf());
        $renderer->setPage($this->getPage());

        $renderer->draw();
    }
}
