<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Api;

/**
 * Command to delete a widget instance by specified widget instance ID
 * @api
 */
interface DeleteWidgetInstanceByIdInterface
{
    /**
     * Delete widget instance by given instance ID
     *
     * @param int $id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function execute(int $id);
}
