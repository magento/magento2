<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ObjectManager\Environment;

use Magento\Framework\App\ObjectManager\Environment\Developer;

class DeveloperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Developer
     */
    protected $_developer;

    protected function setUp()
    {
        $envFactoryMock = $this->getMock('Magento\Framework\App\EnvironmentFactory', [], [], '', false);
        $this->_developer = new Developer($envFactoryMock);
    }

    public function testGetMode()
    {
        $this->assertEquals(Developer::MODE, $this->_developer->getMode());
    }

    public function testGetObjectManagerConfigLoader()
    {
        $this->assertNull($this->_developer->getObjectManagerConfigLoader());
    }
}
