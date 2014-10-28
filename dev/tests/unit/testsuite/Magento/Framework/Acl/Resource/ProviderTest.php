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
namespace Magento\Framework\Acl\Resource;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Acl\Resource\Provider
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_treeBuilderMock;

    protected function setUp()
    {
        $this->_configReaderMock = $this->getMock('Magento\Framework\Config\ReaderInterface');
        $this->_treeBuilderMock = $this->getMock(
            'Magento\Framework\Acl\Resource\TreeBuilder',
            array(),
            array(),
            '',
            false
        );
        $this->_model = new \Magento\Framework\Acl\Resource\Provider($this->_configReaderMock, $this->_treeBuilderMock);
    }

    public function testGetIfAclResourcesExist()
    {
        $aclResourceConfig['config']['acl']['resources'] = array('ExpectedValue');
        $this->_configReaderMock->expects($this->once())->method('read')->will($this->returnValue($aclResourceConfig));
        $this->_treeBuilderMock->expects($this->once())->method('build')->will($this->returnValue('ExpectedResult'));
        $this->assertEquals('ExpectedResult', $this->_model->getAclResources());
    }

    public function testGetIfAclResourcesEmpty()
    {
        $this->_configReaderMock->expects($this->once())->method('read')->will($this->returnValue(array()));
        $this->_treeBuilderMock->expects($this->never())->method('build');
        $this->assertEquals(array(), $this->_model->getAclResources());
    }
}
