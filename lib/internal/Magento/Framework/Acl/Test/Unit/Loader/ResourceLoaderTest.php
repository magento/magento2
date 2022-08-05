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
     * Test for ResourceLoader::populateAcl
     *
     * @return void
     */
    public function testPopulateAclOnValidObjects(): void
    {
        /** @var $aclResource AclResource */
        $aclResource = $this->createMock(AclResource::class);

        /** @var Acl $acl */
        $acl = $this->createPartialMock(Acl::class, ['addResource']);
        $acl->expects($this->exactly(2))->method('addResource');
        $acl
            ->method('addResource')
            ->withConsecutive([$aclResource, null], [$aclResource, $aclResource])
            ->willReturnOnConsecutiveCalls($acl, $acl);

        $factoryObject = $this->createPartialMock(AclResourceFactory::class, ['createResource']);
        $factoryObject->expects($this->any())->method('createResource')->willReturn($aclResource);

        /** @var $resourceProvider ProviderInterface */
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
                                'children' => []
                            ]
                        ]
                    ]
                ]
            );

        $loaderResource = new ResourceLoader($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }

    /**
     * Test for ResourceLoader::populateAcl
     *
     * @return void
     */
    public function testPopulateAclWithException(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Missing ACL resource identifier');
        /** @var $aclResource AclResource */
        $aclResource = $this->createMock(AclResource::class);

        $factoryObject = $this->getMockBuilder(AclResourceFactory::class)
            ->onlyMethods(['createResource'])
            ->disableOriginalConstructor()
            ->getMock();

        $factoryObject->expects($this->any())->method('createResource')->willReturn($aclResource);

        /** @var $resourceProvider ProviderInterface */
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
                                'children' => []
                            ]
                        ]
                    ]
                ]
            );

        /** @var Acl $acl */
        $acl = $this->createPartialMock(Acl::class, ['addResource']);

        $loaderResource = new ResourceLoader($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }
}
