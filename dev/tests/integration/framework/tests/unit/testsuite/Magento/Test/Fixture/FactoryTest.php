<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\TestFramework\Fixture\CallableDataFixture;
use Magento\TestFramework\Fixture\DataFixtureFactory;
use Magento\TestFramework\Fixture\LegacyDataFixture;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test data fixture type factory
 */
class FactoryTest extends TestCase
{
    /**
     * @var DataFixtureFactory
     */
    private $model;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('create')
            ->willReturnCallback([$this, 'createFixture']);
        $this->model = new DataFixtureFactory($objectManager);
    }

    /**
     * Test that callable data fixture is created
     */
    public function testShouldCreateCallableDataFixture(): void
    {
        $this->assertInstanceOf(
            CallableDataFixture::class,
            $this->model->create(get_class($this) . '::' . 'tearDownAfterClass')
        );
    }

    /**
     * Test that legacy data fixture is created
     */
    public function testShouldCreateLegacyDataFixture(): void
    {
        $this->assertInstanceOf(LegacyDataFixture::class, $this->model->create('path/to/fixture.php'));
    }

    /**
     * Test that class based data fixture is created
     */
    public function testShouldCreateDataFixture(): void
    {
        $this->assertInstanceOf(
            RevertibleDataFixtureInterface::class,
            $this->model->create(RevertibleDataFixtureInterface::class)
        );
    }

    /**
     * Create mock of provided class name
     *
     * @param string $className
     * @return MockObject
     */
    public function createFixture(string $className): MockObject
    {
        return $this->createMock($className);
    }
}
