<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Class ShipmentValidator
 * @since 2.2.0
 */
class ShipmentValidator implements ShipmentValidatorInterface
{
    /**
     * @var \Magento\Sales\Model\Validator
     * @since 2.2.0
     */
    private $validator;

    /**
     * ShipmentValidator constructor.
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
    public function validate(ShipmentInterface $entity, array $validators)
    {
        return $this->validator->validate($entity, $validators);
    }
}
