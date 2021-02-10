<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\MagentoDataFixtureBeforeTransaction;

use Magento\TestModuleOverrideConfig\AbstractOverridesTest;
use Magento\TestModuleOverrideConfig\Model\FixtureCallStorage;

/**
 * Checks that magentoDataFixture can be removed using override config
 *
 * @magentoAppIsolation enabled
 */
class RemoveFixtureTest extends AbstractOverridesTest
{
    /** @var FixtureCallStorage */
    private $fixtureCallStorage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureCallStorage = $this->objectManager->get(FixtureCallStorage::class);
    }

    /**
     * Checks that fixture can be removed in global node
     *
     * @magentoDataFixtureBeforeTransaction Magento/TestModuleOverrideConfig/_files/fixture1_first_module.php
     *
     * @return void
     */
    public function testRemoveFixture(): void
    {
        $this->assertNull($this->fixtureCallStorage->getFixturePosition('fixture1_first_module.php'));
    }
}
