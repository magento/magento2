<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Validation;

use Laminas\Validator\NotEmpty;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\NotEmptyFactory;

/**
 * Validator for store name.
 */
class StoreNameValidator extends AbstractValidator
{
    /**
     * @var NotEmptyFactory
     */
    private $notEmptyValidatorFactory;

    /**
     * @param NotEmptyFactory $notEmptyValidatorFactory
     */
    public function __construct(NotEmptyFactory $notEmptyValidatorFactory)
    {
        $this->notEmptyValidatorFactory = $notEmptyValidatorFactory;
    }

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $validator = $this->notEmptyValidatorFactory->create(['options' => []]);
        $validator->setMessage(
            __('Name is required'),
            NotEmpty::IS_EMPTY
        );
        $result = $validator->isValid($value);
        $this->_messages = $validator->getMessages();

        return $result;
    }
}
