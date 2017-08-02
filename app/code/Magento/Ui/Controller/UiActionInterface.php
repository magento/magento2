<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller;

/**
 * Interface UiActionInterface
 * @since 2.0.0
 */
interface UiActionInterface
{
    /**
     * Execute action
     *
     * @return mixed
     * @since 2.0.0
     */
    public function executeAjaxRequest();
}
