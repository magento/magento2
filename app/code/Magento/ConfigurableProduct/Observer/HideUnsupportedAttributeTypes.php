<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\ConfigurableProduct\Observer\HideUnsupportedAttributeTypes
 *
 */
class HideUnsupportedAttributeTypes implements ObserverInterface
{
    /**
     * @var string[]
     */
    protected $supportedTypes = [];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param string[] $supportedTypes
     * @param RequestInterface $request
     */
    public function __construct(array $supportedTypes, RequestInterface $request)
    {
        $this->supportedTypes = $supportedTypes;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->isVariationsPopupUsed()) {
            return;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $observer->getForm();

        $filteredValues = [];
        /** @var \Magento\Framework\Data\Form\Element\Select $frontendInput */
        $frontendInput = $form->getElement('frontend_input');
        foreach ($frontendInput->getValues() as $frontendValue) {
            if (in_array($frontendValue['value'], $this->supportedTypes, true)) {
                $filteredValues[] = $frontendValue;
            }
        }
        $frontendInput->setValues($filteredValues);
    }

    /**
     * @return bool
     */
    private function isVariationsPopupUsed()
    {
        $popup = $this->request->getParam('popup');
        $productTab = $this->request->getParam('product_tab') === 'variations';
        return $popup && $productTab;
    }
}
