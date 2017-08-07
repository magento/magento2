<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\ResourceModel;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\ResourceModel\Page as PageResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Cms\Model\Page;
use Magento\Framework\App\ResourceConnection;

/**
 * Class PageTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageResourceModel
     */
    protected $model;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTimeMock;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManagerMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourcesMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourcesMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getResources')
            ->willReturn($this->resourcesMock);

        $this->model = (new ObjectManager($this))->getObject(PageResourceModel::class, [
            'context' => $this->contextMock,
            'storeManager' => $this->storeManagerMock,
            'dateTime' => $this->dateTimeMock,
            'entityManager' => $this->entityManagerMock,
            'metadataPool' => $this->metadataPoolMock,
        ]);
    }

    public function testSave()
    {
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->pageMock, [])
            ->willReturn(true);

        $this->assertInstanceOf(PageResourceModel::class, $this->model->save($this->pageMock));
    }

    public function testBeforeSave()
    {
        $this->pageMock->expects($this->any())
            ->method('getData')
            ->willReturnMap([
                ['identifier', null, 'test'],
                ['custom_theme_from', null, null],
                ['custom_theme_to', null, '10/02/2016'],
            ]);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with('10/02/2016')
            ->willReturn('10 Feb 2016');
        $this->pageMock->expects($this->any())
            ->method('setData')
            ->withConsecutive(
                ['custom_theme_from', null],
                ['custom_theme_to', '10 Feb 2016']
            );

        $this->model->beforeSave($this->pageMock);
    }
}
