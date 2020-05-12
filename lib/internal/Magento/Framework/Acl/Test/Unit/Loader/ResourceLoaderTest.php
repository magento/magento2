<?php declare(strict_types=1);
/**
 * Test for \Magento\Framework\Acl\Loader\ResourceLoader
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\Loader;

use Magento\Framework\Acl;
use Magento\Framework\Acl\AclResource;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\AclResourceFactory;
use Magento\Framework\Acl\Loader\ResourceLoader;
use PHPUnit\Framework\TestCase;

class ResourceLoaderTest extends TestCase
{
    /**
     * Test for \Magento\Framework\Acl\Loader\ResourceLoader::populateAcl
     */
    public function testPopulateAclOnValidObjects()
    {
        /** @var $aclResource \Magento\Framework\Acl\AclResource */
        $aclResource = $this->createMock(AclResource::class);

        /** @var Acl $acl */
        $acl = $this->createPartialMock(Acl::class, ['addResource']);
        $acl->expects($this->exactly(2))->method('addResource');
        $acl->expects($this->at(0))->method('addResource')->with($aclResource, null)->willReturnSelf();
        $acl->expects($this->at(1))->method('addResource')->with($aclResource, $aclResource)->willReturnSelf();

        $factoryObject = $this->createPartialMock(AclResourceFactory::class, ['createResource']);
        $factoryObject->expects($this->any())->method('createResource')->willReturn($aclResource);

        /** @var $resourceProvider \Magento\Framework\Acl\AclResource\ProviderInterface */
        $resourceProvider = $this->getMockForAbstractClass(ProviderInterface::class);
        $resourceProvider->expects($this->once())
            ->method('getAclResources')
            ->willReturn(
                [
                    [
                        'id' => 'parent_resource::id',
                        'title' => 'Parent Resource Title',
                        'sortOrder' => 10,
                        'children' => [
                            [
                                'id' => 'child_resource::id',
                                'title' => 'Child Resource Title',
                                'sortOrder' => 10,
                                'children' => [],
                            ],
                        ],
                    ],
                ]
            );

        /** @var $loaderResource \Magento\Framework\Acl\Loader\ResourceLoader */
        $loaderResource = new ResourceLoader($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }

    /**
     * Test for \Magento\Framework\Acl\Loader\ResourceLoader::populateAcl
     */
    public function testPopulateAclWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Missing ACL resource identifier');
        /** @var $aclResource \Magento\Framework\Acl\AclResource */
        $aclResource = $this->createMock(AclResource::class);

        $factoryObject = $this->getMockBuilder(AclResourceFactory::class)
            ->setMethods(['createResource'])
            ->disableOriginalConstructor()
            ->getMock();

        $factoryObject->expects($this->any())->method('createResource')->willReturn($aclResource);

        /** @var $resourceProvider \Magento\Framework\Acl\AclResource\ProviderInterface */
        $resourceProvider = $this->getMockForAbstractClass(ProviderInterface::class);
        $resourceProvider->expects($this->once())
            ->method('getAclResources')
            ->willReturn(
                [
                    [
                        'title' => 'Parent Resource Title',
                        'sortOrder' => 10,
                        'children' => [
                            [
                                'id' => 'child_resource::id',
                                'title' => 'Child Resource Title',
                                'sortOrder' => 10,
                                'children' => [],
                            ],
                        ],
                    ],
                ]
            );

        /** @var Acl $acl */
        $acl = $this->createPartialMock(Acl::class, ['addResource']);

        /** @var $loaderResource \Magento\Framework\Acl\Loader\ResourceLoader */
        $loaderResource = new ResourceLoader($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }
}
