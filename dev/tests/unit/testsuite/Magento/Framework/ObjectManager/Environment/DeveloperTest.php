<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager\Environment;

class DeveloperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\Environment\Developer
     */
    protected $_developer;

    protected function setUp()
    {
        $envFactoryMock = $this->getMock('Magento\Framework\ObjectManager\EnvironmentFactory', [], [], '', false);
        $this->_developer = new \Magento\Framework\ObjectManager\Environment\Developer($envFactoryMock);
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
