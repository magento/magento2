<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

/**
 * Class \Magento\Framework\Code\Validator
 *
 * @since 2.0.0
 */
class Validator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     * @since 2.0.0
     */
    protected $_validators = [];

    /**
     * Add validator
     *
     * @param ValidatorInterface $validator
     * @return void
     * @since 2.0.0
     */
    public function add(ValidatorInterface $validator)
    {
        $this->_validators[] = $validator;
    }

    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     * @since 2.0.0
     */
    public function validate($className)
    {
        foreach ($this->_validators as $validator) {
            $validator->validate($className);
        }
    }
}
