<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Type;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Fixture\Type\DataFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class based data fixture
 */
class DataFixtureTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var DataFixtureInterface|MockObject
     */
    private $testFixture;

    /**
     * @var RevertibleDataFixtureInterface|MockObject
     */
    private $testRevertibleFixture;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->testFixture = $this->getMockBuilder(DataFixtureInterface::class)
            ->addMethods(['revert'])
            ->getMockForAbstractClass();
        $this->testRevertibleFixture = $this->createMock(RevertibleDataFixtureInterface::class);
        $this->objectManager->method('create')
            ->willReturnMap(
                [
                    [get_class($this->testFixture), [], $this->testFixture],
                    [get_class($this->testRevertibleFixture), [], $this->testRevertibleFixture],
                ]
            );
    }

    /**
     * Test apply with not revertible fixture
     */
    public function testApplyNotRevertibleFixture(): void
    {
        $data = ['tesKey' => 'testVal'];
        $model = new DataFixture($this->objectManager, get_class($this->testFixture));
        $this->testFixture->expects($this->once())
            ->method('apply')
            ->with($data);
        $model->apply($data);
    }

    /**
     * Test apply with revertible fixture
     */
    public function testApplyRevertibleFixture(): void
    {
        $data = ['tesKey' => 'testVal'];
        $model = new DataFixture($this->objectManager, get_class($this->testRevertibleFixture));
        $this->testRevertibleFixture->expects($this->once())
            ->method('apply')
            ->with($data);
        $model->apply($data);
    }

    /**
     * Test revert with not revertible fixture
     */
    public function testRevertNotRevertibleFixture(): void
    {
        $data = ['tesKey' => 'testVal'];
        $model = new DataFixture($this->objectManager, get_class($this->testFixture));
        $this->testFixture->expects($this->never())
            ->method('revert')
            ->with($data);
        $model->revert($data);
    }

    /**
     * Test revert with revertible fixture
     */
    public function testRevertRevertibleFixture(): void
    {
        $data = ['tesKey' => 'testVal'];
        $model = new DataFixture($this->objectManager, get_class($this->testRevertibleFixture));
        $this->testRevertibleFixture->expects($this->once())
            ->method('revert')
            ->with($data);
        $model->revert($data);
    }
}
