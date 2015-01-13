<?php
/**
 * Test for \Magento\Framework\Acl\Loader\Resource
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Loader;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for \Magento\Framework\Acl\Loader\Resource::populateAcl
     */
    public function testPopulateAclOnValidObjects()
    {
        /** @var $aclResource \Magento\Framework\Acl\Resource */
        $aclResource = $this->getMock('Magento\Framework\Acl\Resource', [], [], '', false);

        /** @var $acl \Magento\Framework\Acl */
        $acl = $this->getMock('Magento\Framework\Acl', ['addResource'], [], '', false);
        $acl->expects($this->exactly(2))->method('addResource');
        $acl->expects($this->at(0))->method('addResource')->with($aclResource, null)->will($this->returnSelf());
        $acl->expects($this->at(1))->method('addResource')->with($aclResource, $aclResource)->will($this->returnSelf());

        $factoryObject = $this->getMock(
            'Magento\Framework\Acl\ResourceFactory',
            ['createResource'],
            [],
            '',
            false
        );
        $factoryObject->expects($this->any())->method('createResource')->will($this->returnValue($aclResource));

        /** @var $resourceProvider \Magento\Framework\Acl\Resource\ProviderInterface */
        $resourceProvider = $this->getMock('Magento\Framework\Acl\Resource\ProviderInterface');
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

        /** @var $loaderResource \Magento\Framework\Acl\Loader\Resource */
        $loaderResource = new \Magento\Framework\Acl\Loader\Resource($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }

    /**
     * Test for \Magento\Framework\Acl\Loader\Resource::populateAcl
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing ACL resource identifier
     */
    public function testPopulateAclWithException()
    {
        /** @var $aclResource \Magento\Framework\Acl\Resource */
        $aclResource = $this->getMock('Magento\Framework\Acl\Resource', [], [], '', false);

        $factoryObject = $this->getMockBuilder('Magento\Framework\Acl\ResourceFactory')
            ->setMethods(['createResource'])
            ->disableOriginalConstructor()
            ->getMock();

        $factoryObject->expects($this->any())->method('createResource')->will($this->returnValue($aclResource));

        /** @var $resourceProvider \Magento\Framework\Acl\Resource\ProviderInterface */
        $resourceProvider = $this->getMock('Magento\Framework\Acl\Resource\ProviderInterface');
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

        /** @var $loaderResource \Magento\Framework\Acl\Loader\Resource */
        $loaderResource = new \Magento\Framework\Acl\Loader\Resource($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }
}
