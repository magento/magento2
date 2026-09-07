<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\MagentoApiDataFixture;

use Magento\TestModuleOverrideConfig\AbstractOverridesTest;
use Magento\TestModuleOverrideConfig\Model\FixtureCallStorage;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class checks that magentoConfigFixtures can be placed into certain place using override config
 *
 * @magentoAppIsolation enabled
 */
class SortFixturesTest extends AbstractOverridesTest
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
     * Checks that fixtures can be placed to specific place according to config
     *
     * @magentoApiDataFixture Magento/TestModuleOverrideConfig/_files/fixture1_first_module.php
     * @magentoApiDataFixture Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @magentoApiDataFixture Magento/TestModuleOverrideConfig/_files/fixture3_first_module.php
     * @param array $sortedFixtures
     * @return void
     */
    #[DataProvider('sortFixturesProvider')]
    public function testSortFixtures(array $sortedFixtures): void
    {
        $this->assertEquals($sortedFixtures, $this->fixtureCallStorage->getStorage());
    }

    /**
     * @return array
     */
    public static function sortFixturesProvider(): array
    {
        return [
            'first_data_set' => [
                [
                    'fixture3_second_module.php',
                    'fixture1_first_module.php',
                    'fixture1_second_module.php',
                    'fixture2_first_module.php',
                    'fixture1_third_module.php',
                    'fixture3_first_module.php',
                    'fixture2_second_module.php',
                ],
            ],
            'second_data_set' => [
                [
                    'fixture1_first_module.php',
                    'fixture1_second_module.php',
                    'fixture2_first_module.php',
                    'fixture3_first_module.php',
                    'fixture2_second_module.php',
                ],
            ],
        ];
    }
}
