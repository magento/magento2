<?php
/**
 * Attribure lock state validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute;

/**
 * Class \Magento\Catalog\Model\Attribute\LockValidatorComposite
 *
 * @since 2.0.0
 */
class LockValidatorComposite implements LockValidatorInterface
{
    /**
     * @var LockValidatorInterface[]
     * @since 2.0.0
     */
    protected $validators = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $validators
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $validators = [])
    {
        foreach ($validators as $validator) {
            if (!is_subclass_of($validator, \Magento\Catalog\Model\Attribute\LockValidatorInterface::class)) {
                throw new \InvalidArgumentException($validator . ' does not implements LockValidatorInterface');
            }
            $this->validators[] = $objectManager->get($validator);
        }
    }

    /**
     * Check attribute lock state
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param null $attributeSet
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     * @since 2.0.0
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object, $attributeSet = null)
    {
        foreach ($this->validators as $validator) {
            $validator->validate($object, $attributeSet);
        }
    }
}
