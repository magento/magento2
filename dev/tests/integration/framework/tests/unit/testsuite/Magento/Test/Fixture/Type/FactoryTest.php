<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Type;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\TestFramework\Fixture\Type\CallableDataFixture;
use Magento\TestFramework\Fixture\Type\DataFixture;
use Magento\TestFramework\Fixture\Type\Factory;
use Magento\TestFramework\Fixture\Type\LegacyDataFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test data fixture type factory
 */
class FactoryTest extends TestCase
{
    /**
     * @var Factory
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
        $this->model = new Factory($objectManager);
    }

    /**
     * Test that callable data fixture is created
     */
    public function testShouldCreateCallableDataFixture(): void
    {
        $fakeClass = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fakeMethod'])
            ->getMock();
        $directives = ['name' => [$fakeClass, 'fakeMethod']];
        $this->assertInstanceOf(CallableDataFixture::class, $this->model->create($directives));
    }

    /**
     * Test that legacy data fixture is created
     */
    public function testShouldCreateLegacyDataFixture(): void
    {
        $directives = ['name' => 'path/to/fixture.php'];
        $this->assertInstanceOf(LegacyDataFixture::class, $this->model->create($directives));
    }

    /**
     * Test that class based data fixture is created
     */
    public function testShouldCreateDataFixture(): void
    {
        $fixtureClass = $this->createMock(DataFixtureInterface::class);
        $directives = ['name' => get_class($fixtureClass)];
        $this->assertInstanceOf(DataFixture::class, $this->model->create($directives));
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
