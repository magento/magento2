<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Block\Role\Tab;

/**
 * Class EditTest to cover Magento\User\Block\Role\Tab\Edit
 *
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Block\Role\Tab\Edit */
    protected $model;

    /** @var \Magento\Framework\Acl\RootResource|\PHPUnit_Framework_MockObject_MockObject */
    protected $rootResourceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $rulesCollectionFactoryMock;

    /** @var \Magento\Authorization\Model\Acl\AclRetriever|\PHPUnit_Framework_MockObject_MockObject */
    protected $aclRetrieverMock;

    /** @var \Magento\Framework\Acl\AclResource\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $aclResourceProviderMock;

    /** @var \Magento\Integration\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $integrationDataMock;

    protected function setUp()
    {
        $this->rootResourceMock = $this->getMockBuilder('Magento\Framework\Acl\RootResource')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->rulesCollectionFactoryMock = $this
            ->getMockBuilder('Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->aclRetrieverMock = $this->getMockBuilder('Magento\Authorization\Model\Acl\AclRetriever')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclResourceProviderMock = $this->getMockBuilder('Magento\Framework\Acl\AclResource\ProviderInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->integrationDataMock = $this->getMockBuilder('Magento\Integration\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\User\Block\Role\Tab\Edit',
            [
                'aclRetriever' => $this->aclRetrieverMock,
                'rootResource' => $this->rootResourceMock,
                'rulesCollectionFactory' => $this->rulesCollectionFactoryMock,
                'aclResourceProvider' => $this->aclResourceProviderMock,
                'integrationData' => $this->integrationDataMock
            ]
        );
    }

    public function testGetTree()
    {
        $resources = ['resource1', 'resource2', 'resource3'];
        $mappedResources = ['mapped1', 'mapped2', 'mapped3'];
        $this->aclResourceProviderMock->expects($this->once())->method('getAclResources')->willReturn($resources);
        $this->integrationDataMock->expects($this->once())->method('mapResources')->willReturn($mappedResources);

        $this->assertEquals($mappedResources, $this->model->getTree());
    }
}
