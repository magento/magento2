<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Fixture\LegacyDataFixturePathResolver;
use PHPUnit\Framework\TestCase;

/**
 * Test fixture path resolver for file based data fixture
 */
class LegacyDataFixturePathResolverTest extends TestCase
{
    /**
     * @var LegacyDataFixturePathResolver
     */
    private $model;

    /**
     * @var string
     */
    private static $basePath;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$basePath = dirname(__FILE__, 5);
        if (!defined('INTEGRATION_TESTS_DIR')) {
            define('INTEGRATION_TESTS_DIR', self::$basePath);
        } else {
            self::$basePath = INTEGRATION_TESTS_DIR;
        }
        self::$basePath .= DIRECTORY_SEPARATOR . 'testsuite' . DIRECTORY_SEPARATOR;
        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'Bar_DataFixtureTest',
            self::$basePath . 'Bar' . DIRECTORY_SEPARATOR . 'DataFixtureTest'
        );
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new LegacyDataFixturePathResolver(new ComponentRegistrar());
    }

    /**
     * Test that fixture full path is resolved correctly
     *
     * @param string $fixture
     * @param string $path
     * @dataProvider fixtureDataProvider
     */
    public function testResolve(string $fixture, string $path): void
    {
        $path = str_replace('{{basePath}}', static::$basePath, $path);
        $this->assertEquals($path, $this->model->resolve($fixture));
    }

    /**
     * @return array
     */
    public function fixtureDataProvider(): array
    {
        return [
            [
                'Magento/Test/_files/fixture.php',
                '{{basePath}}Magento/Test/_files/fixture.php'
            ],
            [
                'Bar_DataFixtureTest::foo/bar/baz/fixture.php',
                '{{basePath}}Bar/DataFixtureTest/foo/bar/baz/fixture.php'
            ]
        ];
    }
}
