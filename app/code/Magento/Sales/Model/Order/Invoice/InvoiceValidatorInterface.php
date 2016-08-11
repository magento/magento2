<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Interface InvoiceValidatorInterface
 */
interface InvoiceValidatorInterface
{
    /**
     * @param InvoiceInterface $entity
     * @param ValidatorInterface[] $validators
     * @return string[]
     * @throws DocumentValidationException
     */
    public function validate(InvoiceInterface $entity, array $validators);
}
