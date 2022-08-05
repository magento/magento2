<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\Strategy\Dummy;
use Magento\Framework\App\Cache\Tag\Strategy\Factory;
use Magento\Framework\App\Cache\Tag\Strategy\Identifier;
use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\DataObject\IdentityInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var MockObject|Identifier
     */
    private $identifierStrategy;

    /**
     * @var MockObject|Dummy
     */
    private $dummyStrategy;

    /**
     * @var MockObject|StrategyInterface
     */
    private $customStrategy;

    /**
     * @var Factory
     */
    private $model;

    protected function setUp(): void
    {
        $this->identifierStrategy = $this->createMock(Identifier::class);

        $this->dummyStrategy = $this->createMock(Dummy::class);

        $this->customStrategy = $this->getMockForAbstractClass(
            StrategyInterface::class
        );

        $this->model = new Factory(
            $this->identifierStrategy,
            $this->dummyStrategy,
            ['PDO' => $this->customStrategy]
        );
    }

    public function testGetStrategyWithScalar()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided argument is not an object');
        $this->model->getStrategy('some scalar');
    }

    public function testGetStrategyWithObject()
    {
        $this->assertEquals($this->dummyStrategy, $this->model->getStrategy(new \stdClass()));
    }

    public function testGetStrategyWithIdentityInterface()
    {
        $object = $this->getMockForAbstractClass(IdentityInterface::class);

        $this->assertEquals($this->identifierStrategy, $this->model->getStrategy($object));
    }

    public function testGetStrategyForCustomClass()
    {
        $object = $this->getMockForAbstractClass('\PDO', [], '', false, false, false, []);

        $this->assertEquals($this->customStrategy, $this->model->getStrategy($object));
    }
}
