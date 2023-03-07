<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Weee\Block\Element\Weee\Tax;

class UpdateElementTypesObserver implements ObserverInterface
{
    /**
     * Add custom element type for attributes form
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $response = $observer->getEvent()->getResponse();
        $types = $response->getTypes();
        $types['weee'] = Tax::class;
        $response->setTypes($types);
        return $this;
    }
}
