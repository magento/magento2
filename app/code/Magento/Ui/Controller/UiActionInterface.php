<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
