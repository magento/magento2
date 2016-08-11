<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Class ValidatorRunner
 */
class Validator
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * ValidatorRunner constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
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
