<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Class InvoiceValidatorRunner
 * @since 2.2.0
 */
class InvoiceValidator implements InvoiceValidatorInterface
{
    /**
     * @var \Magento\Sales\Model\Validator
     * @since 2.2.0
     */
    private $validator;

    /**
     * InvoiceValidatorRunner constructor.
     * @param \Magento\Sales\Model\Validator $validator
     * @since 2.2.0
     */
    public function __construct(\Magento\Sales\Model\Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function validate(InvoiceInterface $entity, array $validators)
    {
        return $this->validator->validate($entity, $validators);
    }
}
