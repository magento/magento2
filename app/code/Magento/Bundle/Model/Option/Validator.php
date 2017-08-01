<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Option;

use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\NotEmptyFactory;
use Zend_Validate_Exception;

/**
 * Class \Magento\Bundle\Model\Option\Validator
 *
 * @since 2.0.0
 */
class Validator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var NotEmpty
     * @since 2.0.0
     */
    private $notEmpty;

    /**
     * @param NotEmptyFactory $notEmptyFactory
     * @since 2.0.0
     */
    public function __construct(NotEmptyFactory $notEmptyFactory)
    {
        $this->notEmpty = $notEmptyFactory->create(['options' => NotEmpty::ALL]);
    }

    /**
     * @param \Magento\Bundle\Model\Option $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     * @since 2.0.0
     */
    public function isValid($value)
    {
        $this->validateRequiredFields($value);

        return !$this->hasMessages();
    }

    /**
     * @param \Magento\Bundle\Model\Option $value
     * @return void
     * @throws Zend_Validate_Exception
     * @throws \Exception
     * @since 2.0.0
     */
    protected function validateRequiredFields($value)
    {
        $messages = [];
        $requiredFields = [
            'title' => $value->getTitle(),
            'type' => $value->getType()
        ];
        foreach ($requiredFields as $requiredField => $requiredValue) {
            if (!$this->notEmpty->isValid(trim($requiredValue))) {
                $messages[$requiredField] = __('%fieldName is a required field.', ['fieldName' => $requiredField]);
            }
        }
        $this->_addMessages($messages);
    }
}
