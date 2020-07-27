<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend as ParentBackend;
use Magento\Eav\Model\Entity\Attribute\Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;

/**
 * Default backend model for catalog attributes.
 */
class DefaultBackend extends ParentBackend
{
    /**
     * @var WYSIWYGValidatorInterface
     */
    private $wysiwygValidator;

    /**
     * @param WYSIWYGValidatorInterface $wysiwygValidator
     */
    public function __construct(WYSIWYGValidatorInterface $wysiwygValidator)
    {
        $this->wysiwygValidator = $wysiwygValidator;
    }

    /**
     * Validate user HTML value.
     *
     * @param DataObject $object
     * @return void
     * @throws LocalizedException
     */
    private function validateHtml(DataObject $object): void
    {
        $attribute = $this->getAttribute();
        $code = $attribute->getAttributeCode();
        if ($attribute instanceof Attribute && $attribute->getIsHtmlAllowedOnFront()) {
            $value = $object->getData($code);
            if ($value
                && is_string($value)
                && (!($object instanceof AbstractModel) || $object->getData($code) !== $object->getOrigData($code))
            ) {
                try {
                    $this->wysiwygValidator->validate($object->getData($code));
                } catch (ValidationException $exception) {
                    $attributeException = new Exception(
                        __(
                            'Using restricted HTML elements for "%1". %2',
                            $attribute->getName(),
                            $exception->getMessage()
                        ),
                        $exception
                    );
                    $attributeException->setAttributeCode($code)->setPart('backend');
                    throw $attributeException;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);
        $this->validateHtml($object);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate($object)
    {
        $isValid = parent::validate($object);
        if ($isValid) {
            $this->validateHtml($object);
        }

        return $isValid;
    }
}
