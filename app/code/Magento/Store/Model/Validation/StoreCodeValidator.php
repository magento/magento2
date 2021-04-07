<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Validation;

use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\RegexFactory;

/**
 * Validator for store code.
 */
class StoreCodeValidator extends AbstractValidator
{
    /**
     * @var RegexFactory
     */
    private $regexValidatorFactory;

    /**
     * @param RegexFactory $regexValidatorFactory
     */
    public function __construct(RegexFactory $regexValidatorFactory)
    {
        $this->regexValidatorFactory = $regexValidatorFactory;
    }

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $validator = $this->regexValidatorFactory->create(['pattern' => '/^[a-z]+[a-z0-9_]*$/i']);
        $validator->setMessage(
            __(
                'The store code may contain only letters (a-z), numbers (0-9) or underscore (_),'
                . ' and the first character must be a letter.'
            ),
            \Zend_Validate_Regex::NOT_MATCH
        );
        $result = $validator->isValid($value);
        $this->_messages = $validator->getMessages();

        return $result;
    }
}
