<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Weee\Observer\UpdateElementTypesObserver
 *
 */
class UpdateElementTypesObserver implements ObserverInterface
{
    /**
     * Add custom element type for attributes form
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponse();
        $types = $response->getTypes();
        $types['weee'] = \Magento\Weee\Block\Element\Weee\Tax::class;
        $response->setTypes($types);
        return $this;
    }
}
