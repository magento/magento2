<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Option;

use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\NotEmptyFactory;
use Magento\Framework\Validator\ValidateException;

class Validator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var NotEmpty
     */
    private $notEmpty;

    /**
     * @param NotEmptyFactory $notEmptyFactory
     */
    public function __construct(NotEmptyFactory $notEmptyFactory)
    {
        $this->notEmpty = $notEmptyFactory->create(['options' => NotEmpty::ALL]);
    }

    /**
     * This method check is valid value.
     *
     * @param \Magento\Bundle\Model\Option $value
     *
     * @return boolean
     * @throws ValidateException
     */
    public function isValid($value)
    {
        $this->validateRequiredFields($value);

        return !$this->hasMessages();
    }

    /**
     * This method  validate required fields.
     *
     * @param \Magento\Bundle\Model\Option $value
     *
     * @return void
     * @throws \Exception|ValidateException
     */
    protected function validateRequiredFields($value)
    {
        $messages = [];
        $requiredFields = [
            'title' => $value->getTitle(),
            'type' => $value->getType()
        ];
        foreach ($requiredFields as $requiredField => $requiredValue) {
            if (!$this->notEmpty->isValid(trim((string) $requiredValue))) {
                $messages[$requiredField] =
                    __('"%fieldName" is required. Enter and try again.', ['fieldName' => $requiredField]);
            }
        }
        $this->_addMessages($messages);
    }
}
