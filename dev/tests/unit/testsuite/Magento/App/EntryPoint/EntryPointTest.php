<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\App\EntryPoint;

class EntryPointTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\EntryPoint\EntryPoint
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var string
     */
    protected $_rootDir;

    /**
     * @var array()
     */
    protected $_parameters;

    protected function setUp()
    {
        $this->_parameters = array(
            'MAGE_MODE' => 'developer',
        );
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_responseMock = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $this->_rootDir = realpath(__DIR__ . '/../../../../../../../');
        $this->_model = new \Magento\App\EntryPoint\EntryPoint(
            $this->_rootDir,
            $this->_parameters,
            $this->_objectManagerMock
        );
    }

    public function testRunExecutesApplication()
    {
        $applicationName = '\Magento\App\TestApplication';
        $applicationMock = $this->getMock('\Magento\LauncherInterface');
        $applicationMock->expects($this->once())->method('launch')->will($this->returnValue($this->_responseMock));
        $this->_objectManagerMock->expects($this->once())->method('create')->with($applicationName, array())
            ->will($this->returnValue($applicationMock));
        $this->assertNull($this->_model->run($applicationName));
    }

    public function testRunCatchesExceptionThrownByApplication()
    {
        $applicationName = '\Magento\App\TestApplication';
        $applicationMock = $this->getMock('\Magento\LauncherInterface');
        $applicationMock->expects($this->once())
            ->method('launch')
            ->will($this->throwException(new \Exception('Something went wrong.')));
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with($applicationName, array())
            ->will($this->returnValue($applicationMock));
        // clean output
        ob_start();
        $this->assertNull($this->_model->run($applicationName));
        ob_end_clean();
    }
}
