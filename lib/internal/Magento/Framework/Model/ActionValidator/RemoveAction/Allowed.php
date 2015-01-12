<?php
/**
 * Action validator for remove action
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ActionValidator\RemoveAction;

class Allowed extends \Magento\Framework\Model\ActionValidator\RemoveAction
{
    /**
     * Safeguard function that checks if item can be removed
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAllowed(\Magento\Framework\Model\AbstractModel $model)
    {
        return true;
    }
}
