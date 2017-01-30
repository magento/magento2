<?php
/**
 * Test for \Magento\Framework\Acl\Loader\ResourceLoader
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\Loader;

class ResourceLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for \Magento\Framework\Acl\Loader\ResourceLoader::populateAcl
     */
    public function testPopulateAclOnValidObjects()
    {
        /** @var $aclResource \Magento\Framework\Acl\AclResource */
        $aclResource = $this->getMock('Magento\Framework\Acl\AclResource', [], [], '', false);

        /** @var $acl \Magento\Framework\Acl */
        $acl = $this->getMock('Magento\Framework\Acl', ['addResource'], [], '', false);
        $acl->expects($this->exactly(2))->method('addResource');
        $acl->expects($this->at(0))->method('addResource')->with($aclResource, null)->will($this->returnSelf());
        $acl->expects($this->at(1))->method('addResource')->with($aclResource, $aclResource)->will($this->returnSelf());

        $factoryObject = $this->getMock(
            'Magento\Framework\Acl\AclResourceFactory',
            ['createResource'],
            [],
            '',
            false
        );
        $factoryObject->expects($this->any())->method('createResource')->will($this->returnValue($aclResource));

        /** @var $resourceProvider \Magento\Framework\Acl\AclResource\ProviderInterface */
        $resourceProvider = $this->getMock('Magento\Framework\Acl\AclResource\ProviderInterface');
        $resourceProvider->expects($this->once())
            ->method('getAclResources')
            ->will(
                $this->returnValue(
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
                )
            );

        /** @var $loaderResource \Magento\Framework\Acl\Loader\ResourceLoader */
        $loaderResource = new \Magento\Framework\Acl\Loader\ResourceLoader($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }

    /**
     * Test for \Magento\Framework\Acl\Loader\ResourceLoader::populateAcl
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing ACL resource identifier
     */
    public function testPopulateAclWithException()
    {
        /** @var $aclResource \Magento\Framework\Acl\AclResource */
        $aclResource = $this->getMock('Magento\Framework\Acl\AclResource', [], [], '', false);

        $factoryObject = $this->getMockBuilder('Magento\Framework\Acl\AclResourceFactory')
            ->setMethods(['createResource'])
            ->disableOriginalConstructor()
            ->getMock();

        $factoryObject->expects($this->any())->method('createResource')->will($this->returnValue($aclResource));

        /** @var $resourceProvider \Magento\Framework\Acl\AclResource\ProviderInterface */
        $resourceProvider = $this->getMock('Magento\Framework\Acl\AclResource\ProviderInterface');
        $resourceProvider->expects($this->once())
            ->method('getAclResources')
            ->will(
                $this->returnValue(
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
                )
            );

        /** @var $acl \Magento\Framework\Acl */
        $acl = $this->getMock('Magento\Framework\Acl', ['addResource'], [], '', false);

        /** @var $loaderResource \Magento\Framework\Acl\Loader\ResourceLoader */
        $loaderResource = new \Magento\Framework\Acl\Loader\ResourceLoader($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }
}
