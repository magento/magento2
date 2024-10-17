<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\LegacyDataFixturePathResolver;
use Magento\TestFramework\Fixture\LegacyDataFixture;
use PHPUnit\Framework\TestCase;

/**
 * Test file based data fixture
 */
class LegacyDataFixtureTest extends TestCase
{
    /**
     * @var LegacyDataFixture
     */
    private $model;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $pathResolver = $this->createMock(LegacyDataFixturePathResolver::class);
        $fixturePath = 'Magento/Test/Annotation/_files/sample_fixture_three.php';
        $pathResolver->method('resolve')
            ->willReturnCallback([$this, 'getFixtureAbsolutePath']);
        $this->model = new LegacyDataFixture(
            $pathResolver,
            $fixturePath
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        putenv('sample_fixture_three');
    }

    /**
     * Test that the fixture is executed
     */
    public function testApply(): void
    {
        $this->model->apply();
        $this->assertEquals('3', getenv('sample_fixture_three'));
    }

    /**
     * Test that the rollback fixture is executed
     */
    public function testRevert(): void
    {
        $this->model->apply();
        $this->model->revert(new DataObject());
        $this->assertEquals('', getenv('sample_fixture_three'));
    }

    /**
     * Get the absolute path of provided fixture
     *
     * @param string $path
     * @return string
     */
    public function getFixtureAbsolutePath(string $path): string
    {
        return dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . $path;
    }
}
