<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    use \Magento\Framework\MessageQueue\Config\SortedList;

    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * Initialize dependencies.
     *
     * @param array $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $this->sort($validators, ValidatorInterface::class, 'validator');
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
