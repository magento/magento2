<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\Navigation;

class NavigationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocatorMock;

    /**
     * @var Navigation
     */
    private $navigation;

    public function setUp()
    {
        $this->serviceLocatorMock =
            $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);

        $this->navigation = new Navigation($this->serviceLocatorMock);
    }

    public function testGetData()
    {
        $this->serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->will($this->returnValue([
                'nav' => [
                    ['key1' => 'value1'],
                    ['key2' => 'value2'],
                ]
            ]));

        $this->assertEquals([['key1' => 'value1'], ['key2' => 'value2']], $this->navigation->getData());
    }

    public function testGetMenuItems()
    {
        $this->serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->will($this->returnValue([
                'nav' => [
                    ['key1' => 'value1'],
                    ['key2' => 'value2'],
                    ['nav-bar' => 'abc', 'key3' => 'value3'],
                    ['nav-bar' => ''],
                    ['nav-bar' => false],
                ]
            ]));

        $this->assertEquals(
            [['nav-bar' => 'abc', 'key3' => 'value3']],
            array_values($this->navigation->getMenuItems())
        );
    }

    public function testGetMainItems()
    {
        $this->serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->will($this->returnValue([
                'nav' => [
                    ['key1' => 'value1'],
                    ['key2' => 'value2'],
                    ['main' => 'abc', 'key3' => 'value3'],
                    ['main' => ''],
                    ['main' => false],
                ]
            ]));

        $this->assertEquals([['main' => 'abc', 'key3' => 'value3']], array_values($this->navigation->getMainItems()));
    }
}
