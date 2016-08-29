<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Validator constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param object $entity
     * @param ValidatorInterface[] $validators
     * @return string[]
     * @throws ConfigurationMismatchException
     */
    public function validate($entity, array $validators)
    {
        $messages = [];
        foreach ($validators as $validatorName) {
            $validator = $this->objectManager->get($validatorName);
            if (!$validator instanceof ValidatorInterface) {
                throw new ConfigurationMismatchException(
                    __(
                        sprintf('Validator %s is not instance of general validator interface', $validatorName)
                    )
                );
            }
            $messages = array_merge($messages, $validator->validate($entity));
        }

        return $messages;
    }
}
