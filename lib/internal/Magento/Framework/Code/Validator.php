<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code;

class Validator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    protected $_validators = [];

    /**
     * Add validator
     *
     * @param ValidatorInterface $validator
     * @return void
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
     */
    public function validate($className)
    {
        foreach ($this->_validators as $validator) {
            $validator->validate($className);
        }
    }
}
