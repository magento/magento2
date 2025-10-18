<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\GroupedProduct\Model\Order\Pdf\Items\Invoice;

class Grouped extends \Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice
{
    /**
     * Draw process
     *
     * @return void
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
