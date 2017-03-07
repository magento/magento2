<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Class InvoiceValidatorRunner
 */
class InvoiceValidator implements InvoiceValidatorInterface
{
    /**
     * @var \Magento\Sales\Model\Validator
     */
    private $validator;

    /**
     * InvoiceValidatorRunner constructor.
     * @param \Magento\Sales\Model\Validator $validator
     */
    public function __construct(\Magento\Sales\Model\Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function validate(InvoiceInterface $entity, array $validators)
    {
        return $this->validator->validate($entity, $validators);
    }
}
