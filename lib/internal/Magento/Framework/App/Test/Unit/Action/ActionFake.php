<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Action;

use \Magento\Framework\App\Action\Action;

class ActionFake extends Action
{
    /**
     * Fake action to check a method call from a parent
     */
    public function execute()
    {
        $this->_forward(
            ActionTest::ACTION_NAME,
            ActionTest::CONTROLLER_NAME,
            ActionTest::MODULE_NAME,
            ActionTest::$actionParams
        );
        $this->_redirect(ActionTest::FULL_ACTION_NAME, ActionTest::$actionParams);
        return;
    }
}
