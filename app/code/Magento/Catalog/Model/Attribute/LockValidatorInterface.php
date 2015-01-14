<?php
/**
 * Attribure lock state validator interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute;

interface LockValidatorInterface
{
    /**
     * Check attribute lock state
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param null $attributeSet
     * @throws \Magento\Framework\Model\Exception
     *
     * @return void
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object, $attributeSet = null);
}
