<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Exception\DocumentValidationException;

/**
 * Class OrderValidator
 * @since 2.2.0
 */
class OrderValidator implements OrderValidatorInterface
{
    /**
     * @var \Magento\Sales\Model\Validator
     * @since 2.2.0
     */
    private $validator;

    /**
     * OrderValidator constructor.
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
    public function validate(OrderInterface $entity, array $validators)
    {
        return $this->validator->validate($entity, $validators);
    }
}
