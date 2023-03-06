<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Shipment;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Sales\Model\ValidatorInterface;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Model\ValidatorResultInterfaceFactory;

/**
 * Requested shipment items validation interface
 */
class ShipmentItemsValidator implements ShipmentItemsValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @var ValidatorResultInterfaceFactory
     */
    private $validatorResultFactory;

    /**
     * @param ValidatorResultInterfaceFactory $validatorResult
     * @param ValidatorInterface[] $validators
     */
    public function __construct(ValidatorResultInterfaceFactory $validatorResult, array $validators = [])
    {
        $this->validatorResultFactory = $validatorResult;
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $items): ValidatorResultInterface
    {
        $messages = [];
        foreach ($this->validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                throw new ConfigurationMismatchException(
                    __(
                        'The "%1" validator is not an instance of the general validator interface.',
                        get_class($validator)
                    )
                );
            }
            foreach ($items as $item) {
                $messages[] = $validator->validate($item);
            }
        }

        return $this->validatorResultFactory->create(['messages' => array_merge([], ...$messages)]);
    }
}
