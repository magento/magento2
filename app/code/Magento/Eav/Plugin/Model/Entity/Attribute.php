<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Plugin\Model\Entity;

use Magento\Eav\Model\Entity\Attribute as EavEntityAttribute;
use Magento\Eav\Model\Validator\Attribute\Code as AttributeCodeValidator;
use Magento\Framework\Exception\LocalizedException;

class Attribute
{
    /**
     * @var AttributeCodeValidator
     */
    private $attributeCodeValidator;

    /**
     * @param AttributeCodeValidator $attributeCodeValidator
     */
    public function __construct(AttributeCodeValidator $attributeCodeValidator)
    {
        $this->attributeCodeValidator = $attributeCodeValidator;
    }

    /**
     * @param EavEntityAttribute $subject
     * @throws \Zend_Validate_Exception
     * @throws LocalizedException
     */
    public function beforeSave(EavEntityAttribute $subject)
    {
        $attributeCode = $subject->getData('attribute_code')
            ?? $subject->getData('frontend_label')[0];

        if (!$this->attributeCodeValidator->isValid($attributeCode)) {
            throw new LocalizedException(current($this->attributeCodeValidator->getMessages()));
        }
    }
}
