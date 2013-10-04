<?php
/**
 * \Magento\Core\Model\DataService\Invoker
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\DataService;

class GraphTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Fake info for service and classes.
     */
    const TEST_CLASS_NAME = 'TEST_CLASS_NAME';

    const TEST_DATA_SERVICE_NAME = 'TEST_DATA_SERVICE_NAME';

    const TEST_NAMESPACE = 'TEST_NAMESPACE';

    const TEST_NAMESPACE_ALIAS = 'TEST_NAMESPACE_ALIAS';

    /**
     * @var \Magento\Core\Model\DataService\Graph
     */
    protected $_graph;

    /**
     * @var object $_dataServiceMock
     */
    protected $_dataServiceMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_invokerMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_repositoryMock;

    protected function setUp()
    {
        $this->_invokerMock = $this->getMock('Magento\Core\Model\DataService\Invoker', array(), array(), "", false);
        $this->_repositoryMock = $this->getMock(
            'Magento\Core\Model\DataService\Repository', array(), array(), "", false
        );
        $this->_graph = new \Magento\Core\Model\DataService\Graph($this->_invokerMock, $this->_repositoryMock);
        $this->_dataServiceMock = (object)array();
    }

    public function testInit()
    {
        $this->_repositoryMock->expects($this->once())->method('setAlias')->with(
            self::TEST_NAMESPACE,
            self::TEST_DATA_SERVICE_NAME,
            self::TEST_NAMESPACE_ALIAS
        );
        $namespaceConfig
            = array('namespaces' => array(self::TEST_NAMESPACE =>
                                          \Magento\Core\Model\DataService\GraphTest::TEST_NAMESPACE_ALIAS));
        $this->_repositoryMock->expects($this->once())->method("get")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue(null));
        $this->_invokerMock->expects($this->once())->method('getServiceData')->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue($this->_dataServiceMock));
        $this->_repositoryMock->expects($this->once())->method("add")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME),
            $this->equalTo($this->_dataServiceMock)
        );
        $this->_graph->init(
            array(self::TEST_DATA_SERVICE_NAME => $namespaceConfig)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Data reference configuration doesn't have a block to link to
     */
    public function testInitMissingNamespaces()
    {
        $namespaceConfig = array();
        $this->_repositoryMock->expects($this->any())->method("get")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue(null));
        $this->_invokerMock->expects($this->any())->method('getServiceData')->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue($this->_dataServiceMock));
        $this->_repositoryMock->expects($this->any())->method("add")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME),
            $this->equalTo($this->_dataServiceMock)
        );
        $this->_graph->init(
            array(self::TEST_DATA_SERVICE_NAME => $namespaceConfig)
        );
    }

    public function testGet()
    {
        $this->_dataServiceMock = (object)array();
        $this->_repositoryMock->expects($this->once())->method("get")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue($this->_dataServiceMock));
        $this->assertEquals(
            $this->_dataServiceMock,
            $this->_graph->get(self::TEST_DATA_SERVICE_NAME)
        );
    }

    public function testGetChild()
    {
        $this->_dataServiceMock = (object)array();
        $this->_repositoryMock->expects($this->once())->method("get")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue($this->_dataServiceMock));
        $this->assertEquals(
            $this->_dataServiceMock,
            $this->_graph->getChildNode(self::TEST_DATA_SERVICE_NAME)
        );
    }

    public function testGetByNamespace()
    {
        $this->_repositoryMock->expects($this->once())->method('getByNamespace')->with(
            self::TEST_NAMESPACE
        )->will($this->returnValue(self::TEST_DATA_SERVICE_NAME));
        $this->assertEquals(
            self::TEST_DATA_SERVICE_NAME,
            $this->_graph->getByNamespace(self::TEST_NAMESPACE)
        );
    }

    public function testGetArgumentValue()
    {
        $this->_invokerMock->expects($this->once())->method('getArgumentValue')->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue($this->_dataServiceMock));

        $argValue = $this->_graph->getArgumentValue(self::TEST_DATA_SERVICE_NAME);

        $this->assertEquals($this->_dataServiceMock, $argValue);
    }
}
