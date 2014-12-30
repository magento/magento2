<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
            ['create']
        )->getMock();
        $contentFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_contentMock));

        $coreRegistryMock = $this->getMockBuilder(
            'Magento\Framework\Registry'
        )->disableOriginalConstructor()->setMethods(
            ['registry']
        )->getMock();
        $coreRegistryMock->expects($this->any())->method('registry')->will($this->returnValue(1));

        $arguments = ['contentFactory' => $contentFactoryMock, 'coreRegistry' => $coreRegistryMock];
        $this->_model = $this->_helper->getObject('Magento\GoogleShopping\Model\Service', $arguments);
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
