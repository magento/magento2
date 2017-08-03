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
 * @since 2.2.0
 */
class CompositeValidator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     * @since 2.2.0
     */
    private $validators;

    /**
     * Initialize dependencies.
     *
     * @param ValidatorInterface[] $validators
     * @since 2.2.0
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function validate($configData)
    {
        foreach ($this->validators as $validator) {
            $validator->validate($configData);
        }
    }
}
