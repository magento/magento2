<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Class CreditmemoValidator
 * @since 2.2.0
 */
class CreditmemoValidator implements CreditmemoValidatorInterface
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
    public function validate(CreditmemoInterface $entity, array $validators)
    {
        return $this->validator->validate($entity, $validators);
    }
}
