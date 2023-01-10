<?php
/**
 * Action validator, remove action
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ActionValidator;

use Magento\Framework\Model\AbstractModel;

/**
 * @api
 * @since 100.0.2
 */
class RemoveAction
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $protectedModels;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param array $protectedModels
     */
    public function __construct(\Magento\Framework\Registry $registry, array $protectedModels = [])
    {
        $this->registry = $registry;
        $this->protectedModels = $protectedModels;
    }

    /**
     * Safeguard function that checks if item can be removed
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function isAllowed(AbstractModel $model)
    {
        $isAllowed = true;

        if ($this->registry->registry('isSecureArea')) {
            $isAllowed = true;
        } elseif (in_array($this->getBaseClassName($model), $this->protectedModels)) {
            $isAllowed = false;
        }

        return $isAllowed;
    }

    /**
     * Get clean model name without Interceptor and Proxy part and slashes
     * @param object $object
     * @return mixed
     */
    protected function getBaseClassName($object)
    {
        $className = ltrim(get_class($object), "\\");
        $className = str_replace(['\Interceptor', '\Proxy'], [''], $className);

        return $className;
    }
}
