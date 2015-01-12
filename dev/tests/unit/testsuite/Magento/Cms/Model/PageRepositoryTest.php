<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

/**
 * Class PageRepositoryTest
 */
class PageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Resource\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Cms\Model\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageFactoryMock;

    /**
     * @var \Magento\Cms\Model\Resource\Page\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageCollectionFactoryMock;

    /**
     * @var \Magento\Framework\DB\QueryBuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilderFactoryMock;

    /**
     * @var \Magento\Framework\DB\MapperFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapperFactoryMock;

    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    protected $pageRepository;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->resourceMock = $this->getMock(
            'Magento\Cms\Model\Resource\Page',
            ['save', 'load', 'delete'],
            [],
            '',
            false
        );
        $this->pageFactoryMock = $this->getMock(
            'Magento\Cms\Model\PageFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->pageCollectionFactoryMock = $this->getMock(
            'Magento\Cms\Model\Resource\Page\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->queryBuilderFactoryMock = $this->getMock(
            'Magento\Framework\DB\QueryBuilderFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->mapperFactoryMock = $this->getMock(
            'Magento\Framework\DB\MapperFactory',
            [],
            [],
            '',
            false
        );

        $this->pageRepository = $objectManager->getObject(
            'Magento\Cms\Model\PageRepository',
            [
                'resource' => $this->resourceMock,
                'pageFactory' => $this->pageFactoryMock,
                'pageCollectionFactory' => $this->pageCollectionFactoryMock,
                'queryBuilderFactory' => $this->queryBuilderFactoryMock,
                'mapperFactory' => $this->mapperFactoryMock
            ]
        );
    }

    /**
     * Run test save method
     *
     * @return void
     */
    public function testSave()
    {
        $pageMock = $this->getMock(
            'Magento\Cms\Model\Page',
            [],
            [],
            '',
            false
        );

        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($pageMock);

        $this->assertEquals($pageMock, $this->pageRepository->save($pageMock));
    }

    /**
     * Run test get method
     *
     * @return void
     */
    public function testGet()
    {
        $id = 20;
        $pageMock = $this->getMockForAbstractClass(
            'Magento\Cms\Model\Page',
            [],
            '',
            false,
            true,
            true,
            ['getId']
        );

        $pageMock->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue($id));
        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($pageMock));
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($pageMock, $id);

        $this->assertEquals($pageMock, $this->pageRepository->get($id));
    }

    /**
     * Run test getList method
     *
     * @return void
     */
    public function testGetList()
    {
        $criteriaMock = $this->getMock(
            'Magento\Cms\Model\Resource\PageCriteria',
            [],
            [],
            '',
            false
        );
        $queryBuilderMock = $this->getMock(
            'Magento\Framework\DB\QueryBuilder',
            ['setCriteria', 'setResource', 'create'],
            [],
            '',
            false
        );
        $queryMock = $this->getMockForAbstractClass(
            'Magento\Framework\DB\QueryInterface',
            [],
            '',
            false
        );
        $collectionMock = $this->getMock(
            'Magento\Cms\Model\Resource\Page\Collection',
            [],
            [],
            '',
            false
        );

        $this->queryBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($queryBuilderMock));
        $queryBuilderMock->expects($this->once())
            ->method('setCriteria')
            ->with($criteriaMock);
        $queryBuilderMock->expects($this->once())
            ->method('setResource')
            ->with($this->resourceMock);
        $queryBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($queryMock));
        $this->pageCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->with(['query' => $queryMock])
            ->will($this->returnValue($collectionMock));

        $this->assertEquals($collectionMock, $this->pageRepository->getList($criteriaMock));
    }

    /**
     * Run test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $pageMock = $this->getMockForAbstractClass(
            'Magento\Cms\Model\Page',
            [],
            '',
            false,
            true,
            true,
            ['getPageId']
        );

        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->with($pageMock);

        $this->assertTrue($this->pageRepository->delete($pageMock));
    }

    /**
     * Run test deleteById method
     *
     * @return void
     */
    public function testDeleteById()
    {
        $id = 20;
        $pageMock = $this->getMockForAbstractClass(
            'Magento\Cms\Model\Page',
            [],
            '',
            false,
            true,
            true,
            ['getId']
        );

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($pageMock));
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($pageMock, $id);
        $pageMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));
        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->with($pageMock);

        $this->assertTrue($this->pageRepository->deleteById($id));
    }
}
