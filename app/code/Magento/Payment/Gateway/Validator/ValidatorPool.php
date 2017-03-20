<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Class ValidatorPool
 * @package Magento\Payment\Gateway\Validator
 * @api
 */
class ValidatorPool implements \Magento\Payment\Gateway\Validator\ValidatorPoolInterface
{
    /**
     * @var ValidatorInterface[] | TMap
     */
    private $validators;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $validators
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $validators = []
    ) {
        $this->validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => ValidatorInterface::class
            ]
        );
    }

    /**
     * Returns configured validator
     *
     * @param string $code
     * @return ValidatorInterface
     * @throws NotFoundException
     */
    public function get($code)
    {
        if (!isset($this->validators[$code])) {
            throw new NotFoundException(__('Validator for field %1 does not exist.', $code));
        }

        return $this->validators[$code];
    }
}
