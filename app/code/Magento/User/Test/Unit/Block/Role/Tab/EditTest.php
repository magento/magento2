<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Block\Role\Tab;

use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Helper\Data;
use Magento\User\Block\Role\Tab\Edit;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EditTest extends TestCase
{
    /** @var Edit */
    protected $model;

    /** @var RootResource|MockObject */
    protected $rootResourceMock;

    /** @var MockObject */
    protected $rulesCollectionFactoryMock;

    /** @var AclRetriever|MockObject */
    protected $aclRetrieverMock;

    /** @var ProviderInterface|MockObject */
    protected $aclResourceProviderMock;

    /** @var Data|MockObject */
    protected $integrationDataMock;

    /** @var Registry|MockObject */
    protected $coreRegistryMock;

    /** @var ObjectManager */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->rootResourceMock = $this->getMockBuilder(RootResource::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->rulesCollectionFactoryMock = $this
            ->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->aclRetrieverMock = $this->getMockBuilder(AclRetriever::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclResourceProviderMock = $this->getMockBuilder(
            ProviderInterface::class
        )->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->integrationDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->coreRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            Edit::class,
            [
                'aclRetriever' => $this->aclRetrieverMock,
                'rootResource' => $this->rootResourceMock,
                'rulesCollectionFactory' => $this->rulesCollectionFactoryMock,
                'aclResourceProvider' => $this->aclResourceProviderMock,
                'integrationData' => $this->integrationDataMock
            ]
        );
        $this->model->setCoreRegistry($this->coreRegistryMock);
    }

    public function testGetTree()
    {
        $resources = [
            ['id' => 'Magento_Backend::admin', 'children' => ['resource1', 'resource2', 'resource3']],
            ['id' => 'Invalid_Node', 'children' => ['resource4', 'resource5', 'resource6']]
        ];
        $mappedResources = ['mapped1', 'mapped2', 'mapped3'];
        $this->aclResourceProviderMock->expects($this->once())->method('getAclResources')->willReturn($resources);
        $this->integrationDataMock->expects($this->once())->method('mapResources')->willReturn($mappedResources);

        $this->assertEquals($mappedResources, $this->model->getTree());
    }

    /**
     * @param bool $isAllowed
     * @dataProvider dataProviderBoolValues
     */
    public function testIsEverythingAllowed($isAllowed)
    {
        $id = 10;

        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with(SaveRole::RESOURCE_ALL_FORM_DATA_SESSION_KEY)
            ->willReturn(true);

        if ($isAllowed) {
            $this->rootResourceMock->expects($this->exactly(2))
                ->method('getId')
                ->willReturnOnConsecutiveCalls($id, $id);
        } else {
            $this->rootResourceMock->expects($this->exactly(2))
                ->method('getId')
                ->willReturnOnConsecutiveCalls(11, $id);
        }

        $this->assertEquals($isAllowed, $this->model->isEverythingAllowed());
    }

    /**
     * @return array
     */
    public function dataProviderBoolValues()
    {
        return [[true], [false]];
    }
}
