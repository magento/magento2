<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\MagentoDataFixtureBeforeTransaction;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleOverrideConfig\Model\FixtureCallStorage;
use PHPUnit\Framework\TestCase;

/**
 * Checks that magentoDataFixture can be removed using override config
 *
 * @magentoAppIsolation enabled
 */
class RemoveFixtureTest extends TestCase
{
    /** @var FixtureCallStorage */
    private $fixtureCallStorage;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->fixtureCallStorage = Bootstrap::getObjectManager()->get(FixtureCallStorage::class);
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
        $this->assertFalse($this->fixtureCallStorage->getFixturePosition('fixture1_first_module.php'));
    }
}
