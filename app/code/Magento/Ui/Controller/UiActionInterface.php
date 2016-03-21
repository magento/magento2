<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller;

/**
 * Interface UiActionInterface
 */
interface UiActionInterface
{
    /**
     * Execute action
     *
     * @return mixed
     */
    public function executeAjaxRequest();
}
