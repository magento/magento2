<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Validator
 *
 * @internal
 * @since 2.2.0
 */
class Validator
{
    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @var ValidatorResultInterfaceFactory
     * @since 2.2.0
     */
    private $validatorResultFactory;

    /**
     * Validator constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ValidatorResultInterfaceFactory $validatorResult
     * @since 2.2.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ValidatorResultInterfaceFactory $validatorResult
    ) {
        $this->objectManager = $objectManager;
        $this->validatorResultFactory = $validatorResult;
    }

    /**
     * @param object $entity
     * @param ValidatorInterface[] $validators
     * @param object|null $context
     * @return ValidatorResultInterface
     * @throws ConfigurationMismatchException
     * @since 2.2.0
     */
    public function validate($entity, array $validators, $context = null)
    {
        $messages = [];
        $validatorArguments = [];
        if ($context !== null) {
            $validatorArguments['context'] = $context;
        }

        foreach ($validators as $validatorName) {
            $validator = $this->objectManager->create($validatorName, $validatorArguments);
            if (!$validator instanceof ValidatorInterface) {
                throw new ConfigurationMismatchException(
                    __(
                        sprintf('Validator %s is not instance of general validator interface', $validatorName)
                    )
                );
            }
            $messages = array_merge($messages, $validator->validate($entity));
        }
        $validationResult = $this->validatorResultFactory->create();
        foreach ($messages as $message) {
            $validationResult->addMessage($message);
        }

        return $validationResult;
    }
}
