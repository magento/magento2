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
 * Class checks that magentoDataFixtures can be added using override config
 *
 * @magentoAppIsolation enabled
 */
class AddFixtureTest extends AbstractOverridesTest
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
     * Checks that fixture can be added
     *
     * @return void
     */
    public function testAddFixture(): void
    {
        $this->assertEquals(
            1,
            $this->fixtureCallStorage->getFixturesCount('fixture1_second_module.php')
        );
    }
}
