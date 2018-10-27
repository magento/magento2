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
 */
class Validator
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ValidatorResultInterfaceFactory
     */
    private $validatorResultFactory;

    /**
     * Validator constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ValidatorResultInterfaceFactory $validatorResult
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
