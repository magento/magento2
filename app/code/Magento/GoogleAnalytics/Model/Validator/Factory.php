<?php
/**
 * Google Analytics Validator Factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
namespace Magento\GoogleAnalytics\Model\Validator;

use Magento\Framework\Validator\IntUtils;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\UniversalFactory;

/**
 * @api
 * @since 100.0.2
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
     * Create Universal Analytics Tracking Id Validator
     *
     * @param string $trackingId
     * @return \Magento\Framework\Validator
     */
    public function createTrackingIdValidator($trackingId)
    {
        $message = __(
            'Tracking Id value is not valid "%1". Tracking Id should be in this format UA-XXXXXXXX',
            $trackingId
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
                            'arguments' => ['pattern' => '/^UA-[A-Z0-9-]*$/i'],
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
     * Create Google Analytics Measurement Id Validator
     *
     * @param string $measurementId
     * @return \Magento\Framework\Validator
     */
    public function createMeasurementIdValidator($measurementId)
    {
        $message = __(
            'Measurement Id value is not valid "%1". Measurement Id should be in this format G-XXXXXXXX',
            $measurementId
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
                            'arguments' => ['pattern' => '/^G-[A-Z0-9]*/i'],
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
}
