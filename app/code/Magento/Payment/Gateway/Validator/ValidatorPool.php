<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMap;

class ValidatorPool implements \Magento\Payment\Gateway\Validator\ValidatorPoolInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @param TMap $validators
     */
    public function __construct(
        TMap $validators
    ) {
        $this->validators = $validators;
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
