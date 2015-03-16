<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager\Environment;

use Magento\Framework\App\Test\Unit\ObjectManager\Environment\CompiledTesting;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\Environment\Compiled
     */
    protected $_compiled;

    protected function setUp()
    {
        $envFactoryMock = $this->getMock('Magento\Framework\App\EnvironmentFactory', [], [], '', false);
        $this->_compiled = new CompiledTesting($envFactoryMock);
    }

    public function testGetMode()
    {
        $this->assertEquals(Compiled::MODE, $this->_compiled->getMode());
    }

    public function testGetObjectManagerFactory()
    {
        $this->assertInstanceOf(
            'Magento\Framework\ObjectManager\Factory\Compiled',
            $this->_compiled->getObjectManagerFactory(['shared_instances' => []])
        );
    }
}
