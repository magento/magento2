<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Validator\Attribute;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Validator\AbstractValidator;
use InvalidArgumentException;

/**
 * Validate EAV entities using attribute backend models.
 */
class Backend extends AbstractValidator
{
    /**
     * Returns true if and only if $value meets the validation requirements.
     *
     * @param AbstractModel $entity
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isValid($entity)
    {
        $this->_messages = [];
        if (!$entity instanceof AbstractModel) {
            throw new InvalidArgumentException('Model must be extended from \Magento\Framework\Model\AbstractModel');
        }
        /** @var AbstractEntity $resource */
        $resource = $entity->getResource();
        if (!$resource instanceof AbstractEntity) {
            throw new InvalidArgumentException(
                'Model resource must be extended from \Magento\Eav\Model\Entity\AbstractEntity'
            );
        }
        $resource->loadAllAttributes($entity);
        $attributes = $resource->getAttributesByCode();
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        foreach ($attributes as $attribute) {
            $backend = $attribute->getBackend();
            if (!method_exists($backend, 'validate') || !is_callable([$backend, 'validate'])) {
                continue;
            }
            $attributeCode = $attribute->getAttributeCode() ?? '';
            try {
                $result = $backend->validate($entity);
                if (false === $result) {
                    $this->_messages[$attributeCode][] = __(
                        'The value of attribute "%1" is invalid.',
                        $attributeCode
                    );
                } elseif (is_string($result)) {
                    $this->_messages[$attributeCode][] = $result;
                }
            } catch (LocalizedException $e) {
                $this->_messages[$attributeCode][] = $e->getMessage();
            }
        }
        return 0 == count($this->_messages);
    }
}
