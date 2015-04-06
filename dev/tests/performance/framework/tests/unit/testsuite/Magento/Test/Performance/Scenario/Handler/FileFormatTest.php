<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Performance\Scenario\Handler;

class FileFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Performance\Scenario\Handler\FileFormat
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\Performance\Scenario\HandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handler;

    /**
     * @var \Magento\TestFramework\Performance\Scenario
     */
    protected $_scenario;

    protected function setUp()
    {
        $this->_handler = $this->getMockForAbstractClass(
            'Magento\TestFramework\Performance\Scenario\HandlerInterface'
        );
        $this->_object = new \Magento\TestFramework\Performance\Scenario\Handler\FileFormat();
        $this->_object->register('jmx', $this->_handler);
        $this->_scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario',
            'scenario.jmx',
            [],
            [],
            []
        );
    }

    protected function tearDown()
    {
        $this->_handler = null;
        $this->_object = null;
        $this->_scenario = null;
    }

    public function testRegisterGetHandler()
    {
        $this->assertNull($this->_object->getHandler('php'));
        $this->_object->register('php', $this->_handler);
        $this->assertSame($this->_handler, $this->_object->getHandler('php'));
    }

    public function testRunDelegation()
    {
        $reportFile = 'scenario.jtl';
        $this->_handler->expects($this->once())->method('run')->with($this->_scenario, $reportFile);
        $this->_object->run($this->_scenario, $reportFile);
    }

    public function testRunUnsupportedFormat()
    {
        $this->setExpectedException(
            'Magento\Framework\Exception\LocalizedException',
            new \Magento\Framework\Phrase("Unable to run scenario '%1', format is not supported", ['Scenario'])
        );
        $scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario',
            'scenario.txt',
            [],
            [],
            []
        );
        $this->_object->run($scenario);
    }
}
