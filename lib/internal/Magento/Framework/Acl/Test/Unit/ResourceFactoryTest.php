<?php declare(strict_types=1);
/**
 * Test class for \Magento\Framework\Acl\AclResourceFactory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit;

use Magento\Framework\Acl\AclResource;
use Magento\Framework\Acl\AclResourceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ResourceFactoryTest extends TestCase
{
    /**
     * @var AclResourceFactory
     */
    protected $_model;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var AclResource
     */
    protected $_expectedObject;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->_expectedObject = $this->createMock(AclResource::class);

        $this->_model = $helper->getObject(
            AclResourceFactory::class,
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
            AclResource::class,
            $arguments
        )->willReturn(
            $this->_expectedObject
        );
        $this->assertEquals($this->_expectedObject, $this->_model->createResource($arguments));
    }
}
