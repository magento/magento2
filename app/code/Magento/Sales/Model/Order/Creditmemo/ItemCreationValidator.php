<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class ItemCreationValidator
 * @since 2.1.3
 */
class ItemCreationValidator implements ItemCreationValidatorInterface
{
    /**
     * @var \Magento\Sales\Model\Validator
     * @since 2.1.3
     */
    private $validator;

    /**
     * InvoiceValidatorRunner constructor.
     * @param \Magento\Sales\Model\Validator $validator
     * @since 2.1.3
     */
    public function __construct(\Magento\Sales\Model\Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function validate(
        CreditmemoItemCreationInterface $entity,
        array $validators,
        OrderInterface $context = null
    ) {
        return $this->validator->validate($entity, $validators, $context);
    }
}
