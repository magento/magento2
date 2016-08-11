<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Interface InvoiceValidatorInterface
 */
interface InvoiceValidatorInterface
{
    /**
     * @param InvoiceInterface $entity
     * @param array $validators
     * @return string[]
     */
    public function validate(InvoiceInterface $entity, array $validators);
}
