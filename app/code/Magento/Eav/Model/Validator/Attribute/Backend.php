<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Validator\Attribute;

/**
 * Validation EAV entity via EAV attributes' backend models
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Backend extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * Returns true if and only if $value meets the validation requirements.
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isValid($entity)
    {
        $this->_messages = [];
        if (!$entity instanceof \Magento\Framework\Model\AbstractModel) {
            throw new \InvalidArgumentException('Model must be extended from \Magento\Framework\Model\AbstractModel');
        }
        /** @var \Magento\Eav\Model\Entity\AbstractEntity $resource */
        $resource = $entity->getResource();
        if (!$resource instanceof \Magento\Eav\Model\Entity\AbstractEntity) {
            throw new \InvalidArgumentException(
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
            try {
                $result = $backend->validate($entity);
                if (false === $result) {
                    $this->_messages[$attribute->getAttributeCode()][] = __(
                        'The value of attribute "%1" is invalid.',
                        $attribute->getAttributeCode()
                    );
                } elseif (is_string($result)) {
                    $this->_messages[$attribute->getAttributeCode()][] = $result;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messages[$attribute->getAttributeCode()][] = $e->getMessage();
            }
        }
        return 0 == count($this->_messages);
    }
}
