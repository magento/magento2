<?php
/**
 * Google AdWords Validator Factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
namespace Magento\GoogleAdwords\Model\Validator;

use Magento\Framework\Validator\IntUtils;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\UniversalFactory;

/**
 * @api
 */
class Factory
{
    /**
     * @var UniversalFactory
     */
    protected $_validatorBuilderFactory;

    /**
     * @param UniversalFactory $validatorBuilderFactory
     */
    public function __construct(UniversalFactory $validatorBuilderFactory)
    {
        $this->_validatorBuilderFactory = $validatorBuilderFactory;
    }

    /**
     * Create color validator
     *
     * @param string $currentColor
     * @return \Magento\Framework\Validator
     */
    public function createColorValidator($currentColor)
    {
        $message = __(
            'Conversion Color value is not valid "%1". Please set hexadecimal 6-digit value.',
            $currentColor
        );
        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_validatorBuilderFactory->create(
            \Magento\Framework\Validator\Builder::class,
            [
                'constraints' => [
                    [
                        'alias' => 'Regex',
                        'type' => '',
                        'class' => \Magento\Framework\Validator\Regex::class,
                        'options' => [
                            'arguments' => ['pattern' => '/^[0-9a-f]{6}$/i'],
                            'methods' => [
                                [
                                    'method' => 'setMessages',
                                    'arguments' => [
                                        [Regex::NOT_MATCH => $message, Regex::INVALID => $message],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        );
        return $builder->createValidator();
    }

    /**
     * Create Conversion id validator
     *
     * @param int|string $currentId
     * @return \Magento\Framework\Validator
     */
    public function createConversionIdValidator($currentId)
    {
        $message = __('Conversion Id value is not valid "%1". Conversion Id should be an integer.', $currentId);
        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_validatorBuilderFactory->create(
            \Magento\Framework\Validator\Builder::class,
            [
                'constraints' => [
                    [
                        'alias' => 'Int',
                        'type' => '',
                        'class' => \Magento\Framework\Validator\IntUtils::class,
                        'options' => [
                            'methods' => [
                                [
                                    'method' => 'setMessages',
                                    'arguments' => [[IntUtils::NOT_INT => $message, IntUtils::INVALID => $message]],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        );
        return $builder->createValidator();
    }
}
