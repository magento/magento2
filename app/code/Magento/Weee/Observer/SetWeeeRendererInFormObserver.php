<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Data\Form;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Weee\Block\Renderer\Weee\Tax as RendererWeeeTax;
use Magento\Weee\Model\Tax;

class SetWeeeRendererInFormObserver implements ObserverInterface
{
    /**
     * @param LayoutInterface $layout
     * @param Tax $weeeTax
     */
    public function __construct(
        protected LayoutInterface $layout,
        protected Tax $weeeTax
    ) {
    }

    /**
     * Assign custom renderer for product create/edit form weee attribute element
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getEvent()->getForm();

        $attributes = $this->weeeTax->getWeeeAttributeCodes(true);
        foreach ($attributes as $code) {
            $weeeTax = $form->getElement($code);
            if ($weeeTax) {
                $weeeTax->setRenderer($this->layout->createBlock(RendererWeeeTax::class));
            }
        }

        return $this;
    }
}
