<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetWeeeRendererInFormObserver implements ObserverInterface
{
    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $weeeTax;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Weee\Model\Tax $weeeTax
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Weee\Model\Tax $weeeTax
    ) {
        $this->layout = $layout;
        $this->weeeTax = $weeeTax;
    }

    /**
     * Assign custom renderer for product create/edit form weee attribute element
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $observer->getEvent()->getForm();

        $attributes = $this->weeeTax->getWeeeAttributeCodes(true);
        foreach ($attributes as $code) {
            $weeeTax = $form->getElement($code);
            if ($weeeTax) {
                $weeeTax->setRenderer($this->layout->createBlock(\Magento\Weee\Block\Renderer\Weee\Tax::class));
            }
        }

        return $this;
    }
}
