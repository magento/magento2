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
        $this->validators = [];
        $validators = $this->sortValidators($validators);
        foreach ($validators as $name => $validatorInfo) {
            if (!isset($validatorInfo['validator']) || !($validatorInfo['validator'] instanceof ValidatorInterface)) {
                throw new \InvalidArgumentException(
                    new Phrase(
                        'Validator "%name" must implement "%validatorInterface"',
                        ['name' => $name, 'validatorInterface' => ValidatorInterface::class]
                    )
                );
            }
            $this->validators[$name] = $validatorInfo['validator'];
        }
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

    /**
     * Sort validators according to param 'sortOrder'
     *
     * @param array $validators
     * @return array
     */
    private function sortValidators(array $validators)
    {
        usort(
            $validators,
            function ($firstItem, $secondItem) {
                $firstValue = 0;
                $secondValue = 0;
                if (isset($firstItem['sortOrder'])) {
                    $firstValue = intval($firstItem['sortOrder']);
                }

                if (isset($secondItem['sortOrder'])) {
                    $secondValue = intval($secondItem['sortOrder']);
                }

                if ($firstValue == $secondValue) {
                    return 0;
                }
                return $firstValue < $secondValue ? -1 : 1;
            }
        );
        return $validators;
    }
}
