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
namespace Magento\GoogleShopping\Model;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\GoogleShopping\Model\Service
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contentMock;

    protected function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_contentMock = $this->getMockBuilder(
            'Magento\Framework\Gdata\Gshopping\Content'
        )->disableOriginalConstructor()->getMock();
        $contentFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Gdata\Gshopping\ContentFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();
        $contentFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_contentMock));

        $coreRegistryMock = $this->getMockBuilder(
            'Magento\Framework\Registry'
        )->disableOriginalConstructor()->setMethods(
            array('registry')
        )->getMock();
        $coreRegistryMock->expects($this->any())->method('registry')->will($this->returnValue(1));

        $arguments = array('contentFactory' => $contentFactoryMock, 'coreRegistry' => $coreRegistryMock);
        $this->_model = $this->_helper->getObject('\Magento\GoogleShopping\Model\Service', $arguments);
    }

    public function testGetService()
    {
        $this->assertEquals('Magento\Framework\Gdata\Gshopping\Content', get_parent_class($this->_model->getService()));
    }

    public function testSetService()
    {
        $this->_model->setService($this->_contentMock);
        $this->assertSame($this->_contentMock, $this->_model->getService());
    }
}
