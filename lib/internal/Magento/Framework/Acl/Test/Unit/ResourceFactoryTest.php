<?php
/**
 * Test class for \Magento\Framework\Acl\AclResourceFactory
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit;

class ResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Acl\AclResourceFactory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Acl\AclResource
     */
    protected $_expectedObject;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->_expectedObject = $this->getMock(\Magento\Framework\Acl\AclResource::class, [], [], '', false);

        $this->_model = $helper->getObject(
            \Magento\Framework\Acl\AclResourceFactory::class,
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
            \Magento\Framework\Acl\AclResource::class,
            $arguments
        )->will(
            $this->returnValue($this->_expectedObject)
        );
        $this->assertEquals($this->_expectedObject, $this->_model->createResource($arguments));
    }
}
