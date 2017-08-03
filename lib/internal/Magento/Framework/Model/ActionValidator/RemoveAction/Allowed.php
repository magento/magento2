<?php
/**
 * Action validator for remove action
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ActionValidator\RemoveAction;

/**
 * Class \Magento\Framework\Model\ActionValidator\RemoveAction\Allowed
 *
 * @since 2.0.0
 */
class Allowed extends \Magento\Framework\Model\ActionValidator\RemoveAction
{
    /**
     * Safeguard function that checks if item can be removed
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function isAllowed(\Magento\Framework\Model\AbstractModel $model)
    {
        return true;
    }
}
