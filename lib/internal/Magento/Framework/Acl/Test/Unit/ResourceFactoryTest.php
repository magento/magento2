<?php
/**
 * Test class for \Magento\Framework\Acl\AclResourceFactory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit;

class ResourceFactoryTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->_expectedObject = $this->createMock(\Magento\Framework\Acl\AclResource::class);

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
        )->willReturn(
            $this->_expectedObject
        );
        $this->assertEquals($this->_expectedObject, $this->_model->createResource($arguments));
    }
}
