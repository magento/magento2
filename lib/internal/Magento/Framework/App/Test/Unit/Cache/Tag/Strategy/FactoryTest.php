<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache\Tag\Strategy;

use \Magento\Framework\App\Cache\Tag\Strategy\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Tag\Strategy\Identifier
     */
    private $identifierStrategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Tag\Strategy\Dummy
     */
    private $dummyStrategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Tag\StrategyInterface
     */
    private $customStrategy;

    /**
     * @var Factory
     */
    private $model;

    protected function setUp()
    {
        $this->identifierStrategy = $this->getMock(
            \Magento\Framework\App\Cache\Tag\Strategy\Identifier::class,
            [],
            [],
            '',
            false
        );

        $this->dummyStrategy = $this->getMock(
            \Magento\Framework\App\Cache\Tag\Strategy\Dummy::class,
            [],
            [],
            '',
            false
        );

        $this->customStrategy = $this->getMockForAbstractClass(
            \Magento\Framework\App\Cache\Tag\StrategyInterface::class
        );

        $this->model = new Factory(
            $this->identifierStrategy,
            $this->dummyStrategy,
            ['PDO' => $this->customStrategy]
        );
    }

    public function testGetStrategyWithScalar()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Provided argument is not an object');
        $this->model->getStrategy('some scalar');
    }

    public function testGetStrategyWithObject()
    {
        $this->assertEquals($this->dummyStrategy, $this->model->getStrategy(new \StdClass));
    }

    public function testGetStrategyWithIdentityInterface()
    {
        $object = $this->getMockForAbstractClass(\Magento\Framework\DataObject\IdentityInterface::class);

        $this->assertEquals($this->identifierStrategy, $this->model->getStrategy($object));
    }

    public function testGetStrategyForCustomClass()
    {
        $object = $this->getMockForAbstractClass('\PDO', [], '', false, false, false, []);

        $this->assertEquals($this->customStrategy, $this->model->getStrategy($object));
    }
}
