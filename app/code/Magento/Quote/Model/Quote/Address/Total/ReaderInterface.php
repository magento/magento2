<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

/**
 * Interface \Magento\Quote\Model\Quote\Address\Total\ReaderInterface
 *
 * @since 2.0.0
 */
interface ReaderInterface
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return  []
     * @since 2.0.0
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total);
}
