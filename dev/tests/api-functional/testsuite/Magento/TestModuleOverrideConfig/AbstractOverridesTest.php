<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Base class for override config tests.
 */
abstract class AbstractOverridesTest extends WebapiAbstract
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $useConfig = (defined('USE_OVERRIDE_CONFIG') && USE_OVERRIDE_CONFIG === 'enabled');

        if (!$useConfig) {
            $this->markTestSkipped('Override config is disabled.');
        }

        $this->objectManager = Bootstrap::getObjectManager();
    }
}
