<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Turn Off Multishipping mode if enabled.
 */
class DisableMultishipping
{
    /**
     * Disable Multishipping mode for provided Quote and return TRUE if it was changed.
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function execute(CartInterface $quote): bool
    {
        $modeChanged = false;
        if ($quote->getIsMultiShipping()) {
            $quote->setIsMultiShipping(0);
            $extensionAttributes = $quote->getExtensionAttributes();
            if ($extensionAttributes && $extensionAttributes->getShippingAssignments()) {
                $extensionAttributes->setShippingAssignments([]);
            }

            $modeChanged = true;
        }

        return $modeChanged;
    }
}
