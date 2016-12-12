<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager;

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
     * @param ValidatorResultInterfaceFactory|null $validatorResult
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ValidatorResultInterfaceFactory $validatorResult = null
    ) {
        $this->objectManager = $objectManager;
        $this->validatorResultFactory = $validatorResult ?: ObjectManager::getInstance()->get(
            ValidatorResultInterfaceFactory::class
        );
    }

    /**
     * @param object $entity
     * @param ValidatorInterface[] $validators
     * @param object|null $context
     * @return ValidatorResultInterface
     * @throws LocalizedException
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
                throw new LocalizedException(
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
