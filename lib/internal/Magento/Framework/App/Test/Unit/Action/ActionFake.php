<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Action;

use Magento\Framework\App\Action\Action;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
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
    }
}
