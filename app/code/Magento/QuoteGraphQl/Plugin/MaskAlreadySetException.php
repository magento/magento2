<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Magento\Quote\Model\QuoteIdMask;

/**
 * Don't proceed beforeSave method if masked id already set.
 */
class MaskAlreadySetException
{
    /**
     * @param \Magento\Quote\Model\QuoteIdMask $subject
     * @param \Closure                         $proceed
     *
     * @return \Magento\Quote\Model\QuoteIdMask
     */
    public function aroundBeforeSave(QuoteIdMask $subject, \Closure $proceed)
    {
        $maskedId = $subject->getMaskedId();

        if (!$maskedId) {
            $proceed();
        }

        return $subject;
    }
}
