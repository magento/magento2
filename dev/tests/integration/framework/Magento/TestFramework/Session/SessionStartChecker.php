<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Session;

/**
 * Class to check if session can be started or not. Dummy for integration tests.
 */
class SessionStartChecker extends \Magento\Framework\Session\SessionStartChecker
{
    /**
     * Can session be started or not.
     *
     * @return bool
     */
    public function check() : bool
    {
        return true;
    }
}
