<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager;

/**
 * This interface is used to reset service's mutable state, and similar problems, after request has been sent in
 * Stateful application server and can be used in other long running processes where mutable state in services can
 * cause issues.
 */
interface ResetAfterRequestInterface
{
    /**
     * Resets mutable state and/or resources in objects that need to be cleaned after a response has been sent.
     *
     * @return void
     */
    public function _resetState(): void;
}
