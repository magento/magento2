<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingMethodChoose;

/**
 * Container to register available deferred shipping method choosers.
 * Use deferred shipping method code as a key for a deferred chooser.
 *
 * @api
 */
class DeferredShippingMethodChooserPool
{
    private $choosers;

    /**
     * @param DeferredShippingMethodChooserInterface[] $choosers
     */
    public function __construct(array $choosers)
    {
        foreach ($choosers as $chooser) {
            if (!$chooser instanceof DeferredShippingMethodChooserInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid configuration. Chooser should be instance of %s.',
                    DeferredShippingMethodChooserInterface::class
                ));
            }
        }
        $this->choosers = $choosers;
    }

    /**
     * @param string $type
     * @return DeferredShippingMethodChooserInterface
     */
    public function get($type) : DeferredShippingMethodChooserInterface
    {
        if (!isset($this->choosers[$type])) {
            throw new \InvalidArgumentException(sprintf(
                'Deferred shipping method %s is not registered.',
                $type
            ));
        }

        return $this->choosers[$type];
    }
}
