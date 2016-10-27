<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Class CreditmemoValidator
 */
class CreditmemoValidator implements CreditmemoValidatorInterface
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
    public function validate(CreditmemoInterface $entity, array $validators)
    {
        return $this->validator->validate($entity, $validators);
    }
}
