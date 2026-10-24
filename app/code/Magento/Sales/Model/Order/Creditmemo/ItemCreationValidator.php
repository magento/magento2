<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class ItemCreationValidator
 */
class ItemCreationValidator implements ItemCreationValidatorInterface
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
    public function validate(
        CreditmemoItemCreationInterface $entity,
        array $validators,
        ?OrderInterface $context = null
    ) {
        return $this->validator->validate($entity, $validators, $context);
    }
}
