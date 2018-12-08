<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\MessageQueue\Consumer\Config\ValidatorInterface;
use Magento\Framework\Phrase;

/**
 * Composite validator for consumer config.
 */
class CompositeValidator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * Initialize dependencies.
     *
     * @param ValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($configData)
    {
        foreach ($this->validators as $validator) {
            $validator->validate($configData);
        }
    }
}
