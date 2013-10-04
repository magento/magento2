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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Acl\Resource;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\Acl\Resource\Provider
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected  $_configReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_treeBuilderMock;

    protected function setUp()
    {
        $this->_configReaderMock = $this->getMock(
            'Magento\Webapi\Model\Acl\Resource\Config\Reader\Filesystem', array(), array(), '', false
        );
        $this->_configScopeMock = $this->getMock('Magento\Config\ScopeInterface');
        $this->_treeBuilderMock =
            $this->getMock('Magento\Acl\Resource\TreeBuilder', array(), array(), '', false);
        $this->_model = new \Magento\Webapi\Model\Acl\Resource\Provider(
            $this->_configReaderMock,
            $this->_configScopeMock,
            $this->_treeBuilderMock
        );
    }

    public function testGetAclVirtualResources()
    {
        $aclResourceConfig['config']['mapping'] = array('ExpectedValue');
        $this->_configReaderMock->expects($this->once())
            ->method('read')->with(null)->will($this->returnValue($aclResourceConfig));
        $this->assertEquals($aclResourceConfig['config']['mapping'], $this->_model->getAclVirtualResources());
    }
}
