<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Action\Plugin\_files;

use Magento\Framework\Session\SessionManagerInterface;

/**
 * Test double exposing getCurrencyCode (which real Magento session
 * implementations expose via magic data access). Used to make
 * SessionManagerInterface mocks aware of the method needed by
 * Magento\Store\App\Action\Plugin\Context.
 */
abstract class ContextTestSession implements SessionManagerInterface
{
    abstract public function getCurrencyCode();
}
