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
 * Class check that magentoDataFixturesBeforeTransaction can be replaced using override config
 *
 * @magentoAppIsolation enabled
 */
class ReplaceFixtureTest extends TestCase
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
     * Checks that fixture can be replaced in global node
     *
     * @magentoDataFixtureBeforeTransaction Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     *
     * @return void
     */
    public function testReplaceFixture(): void
    {
        $this->assertEquals(0, $this->fixtureCallStorage->getFixturesCount('fixture2_first_module.php'));
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount('fixture3_first_module.php'));
    }
}
