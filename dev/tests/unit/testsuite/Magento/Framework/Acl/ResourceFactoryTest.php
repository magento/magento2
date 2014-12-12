<?php
/**
 * Test class for \Magento\Framework\Acl\ResourceFactory
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Acl;

class ResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Acl\ResourceFactory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Acl\Resource
     */
    protected $_expectedObject;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $this->_expectedObject = $this->getMock('Magento\Framework\Acl\Resource', [], [], '', false);

        $this->_model = $helper->getObject(
            'Magento\Framework\Acl\ResourceFactory',
            ['objectManager' => $this->_objectManager]
        );
    }

    public function testCreateResource()
    {
        $arguments = ['5', '6'];
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\Acl\Resource',
            $arguments
        )->will(
            $this->returnValue($this->_expectedObject)
        );
        $this->assertEquals($this->_expectedObject, $this->_model->createResource($arguments));
    }
}
