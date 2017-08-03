<?php
/**
 * Attribure lock state validator interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute;

/**
 * Interface \Magento\Catalog\Model\Attribute\LockValidatorInterface
 *
 * @since 2.0.0
 */
interface LockValidatorInterface
{
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
    public function validate(\Magento\Framework\Model\AbstractModel $object, $attributeSet = null);
}
