<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\MagentoApiDataFixture;

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
     * Checks that fixtures added in all nodes successfully applied
     *
     * @dataProvider addedFixturesProvider
     *
     * @param array $fixtures
     * @return void
     */
    public function testAddFixtures(array $fixtures): void
    {
        foreach ($fixtures as $scope => $fixture) {
            $this->assertEquals(
                1,
                $this->fixtureCallStorage->getFixturesCount($fixture),
                sprintf('Fixture added in %s scope was not called', $scope)
            );
        }
    }

    /**
     * @return array
     */
    public function addedFixturesProvider(): array
    {
        return [
            'first_data_set' => [
                [
                    'class' => 'fixture1_second_module.php',
                    'method' => 'fixture2_second_module.php',
                    'data_set' => 'fixture3_second_module.php',
                ],
            ],
            'second_data_set' => [
                [
                    'class' => 'fixture1_second_module.php',
                    'method' => 'fixture2_second_module.php',
                ],
            ],
        ];
    }

    /**
     * Checks that same fixture can be added via override config from few files
     *
     * @return void
     */
    public function testAddSameFixtures(): void
    {
        $this->assertEquals(
            3,
            $this->fixtureCallStorage->getFixturesCount('fixture2_second_module.php')
        );
    }

    /**
     * Checks that fixture which require another fixture can be added using override
     *
     * @return void
     */
    public function testAddFixtureWithRequiredFixture(): void
    {
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount('fixture_with_required_fixture.php'));
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount('fixture3_second_module.php'));
    }
}
