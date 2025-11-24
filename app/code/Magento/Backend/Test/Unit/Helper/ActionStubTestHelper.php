<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Helper;

use Magento\Backend\Test\Unit\App\Action\Stub\ActionStub;

/**
 * Test helper for ActionStub with additional test-specific methods
 *
 * This helper extends ActionStub to provide the setDirtyRulesNoticeMessage()
 * method which is used in production by CatalogRule controllers but doesn't
 * exist in the base ActionStub class. Since PHPUnit 12 removed addMethods(),
 * this helper is necessary to test code that calls this method.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class ActionStubTestHelper extends ActionStub
{
    public function __construct()
    {
        // Skip parent constructor for testing
    }
    
    /**
     * Stub method for testing CatalogRule controllers
     *
     * This method is called by AdminGws\Model\Controllers::promoCatalogIndexAction()
     * but doesn't exist in the base ActionStub class.
     *
     * @param string $message
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setDirtyRulesNoticeMessage($message)
    {
        return $this;
    }
}
