<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\Unit\Listener\ReplaceObjectManager;

interface TestProvidesServiceInterface
{
    /**
     * Gets a service object from a test to use by the mock object manager
     *
     * @param string $type
     * @return object|null
     */
    public function getServiceForObjectManager(string $type) : ?object;
}
