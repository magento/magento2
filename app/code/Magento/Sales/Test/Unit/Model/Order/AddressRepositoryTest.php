<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Sales\Model\Order\AddressRepository;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection as OrderAddressCollection;
use Magento\Customer\Model\ResourceModel\Form\Attribute\Collection as FormAttributeCollection;
use Magento\Framework\Api\SearchCriteria;
use Magento\Sales\Api\Data\OrderAddressSearchResultInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\Order\AddressRepository as OrderAddressRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Unit test for order address repository class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepositoryTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var OrderAddressRepository
     */
    protected $subject;

    /**
     * Sales resource metadata.
     *
     * @var Metadata|MockObject
     */
    protected $metadata;

    /**
     * @var OrderAddressSearchResultInterfaceFactory|MockObject
     */
    protected $searchResultFactory;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var Attribute[]
     */
    private $attributesList;

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var OrderAddress|MockObject
     */
    private $orderAddress;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->orderAddress = $this->createPartialMock(OrderAddress::class, ['getEntityId', 'load']);
        $this->metadata = $this->createPartialMock(
            Metadata::class,
            ['getNewInstance', 'getMapper']
        );

        $this->attributeMetadataDataProvider = $this->getMockBuilder(AttributeMetadataDataProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAttributesCollection'])
            ->getMock();
        $collectionAttribute = $this->getMockBuilder(FormAttributeCollection::class)
            ->setMethods(['addFieldToFilter', 'getIterator'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionAttribute->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
        $this->attributeMetadataDataProvider->method('loadAttributesCollection')->willReturn($collectionAttribute);

        $this->searchResultFactory = $this->createPartialMock(
            OrderAddressSearchResultInterfaceFactory::class,
            ['create']
        );

        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $this->subject = $this->objectManager->getObject(
            OrderAddressRepository::class,
            [
                'metadata' => $this->metadata,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionProcessor' => $this->collectionProcessorMock,
                'attributeMetadataDataProvider' => $this->attributeMetadataDataProvider
            ]
        );
    }

    /**
     * Test for get order address
     *
     * @param int|null $id
     * @param int|null $entityId
     *
     * @return void
     * @dataProvider getDataProvider
     */
    public function testGet(?int $id, ?int $entityId): void
    {
        if (!$id) {
            $this->expectException(InputException::class);
            $this->subject->get($id);
        } else {

            $this->orderAddress->expects($this->once())
                ->method('load')
                ->with($id)
                ->willReturn($this->orderAddress);
            $this->orderAddress->expects($this->once())
                ->method('getEntityId')
                ->willReturn($entityId);

            $this->metadata->expects($this->once())
                ->method('getNewInstance')
                ->willReturn($this->orderAddress);

            if (!$entityId) {
                $this->expectException(NoSuchEntityException::class);
                $this->subject->get($id);
            } else {
                $this->assertEquals($this->orderAddress, $this->subject->get($id));

                $this->orderAddress->expects($this->never())
                    ->method('load')
                    ->with($id)
                    ->willReturn($this->orderAddress);
                $this->orderAddress->expects($this->never())
                    ->method('getEntityId')
                    ->willReturn($entityId);

                $this->metadata->expects($this->never())
                    ->method('getNewInstance')
                    ->willReturn($this->orderAddress);

                // Retrieve Address from registry.
                $this->assertEquals($this->orderAddress, $this->subject->get($id));
            }
        }
    }

    /**
     * Data for testGet
     *
     * @return array
     */
    public function getDataProvider(): array
    {
        return [
            [null, null],
            [1, null],
            [1, 1]
        ];
    }

    /**
     * Test for get list order address
     *
     * @return void
     */
    public function testGetList(): void
    {
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $collection = $this->createMock(OrderAddressCollection::class);

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $this->searchResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals($collection, $this->subject->getList($searchCriteria));
    }

    /**
     * Test for delete order address
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->orderAddress->expects($this->once())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['delete']
        );
        $mapper->expects($this->once())
            ->method('delete')
            ->with($this->orderAddress);

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertTrue($this->subject->delete($this->orderAddress));
    }

    /**
     * Test for delete order address with exception
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage The order address couldn't be deleted.
     */
    public function testDeleteWithException(): void
    {
        $this->orderAddress->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['delete']
        );
        $mapper->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('error'));

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->subject->delete($this->orderAddress);
    }

    /**
     * Test for save order address
     *
     * @return void
     */
    public function testSave(): void
    {
        $this->orderAddress->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['save']
        );
        $mapper->expects($this->once())
            ->method('save')
            ->with($this->orderAddress);

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($this->orderAddress, $this->subject->save($this->orderAddress));
    }

    /**
     * Test for save order address with exception
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage The order address couldn't be saved.
     */
    public function testSaveWithException(): void
    {
        $this->orderAddress->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['save']
        );
        $mapper->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('error'));

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($this->orderAddress, $this->subject->save($this->orderAddress));
    }

    /**
     * Tets for create order address
     *
     * @return void
     */
    public function testCreate(): void
    {
        $this->metadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($this->orderAddress);

        $this->assertEquals($this->orderAddress, $this->subject->create());
    }

    /**
     * Test for save sales address with multi-attribute.
     *
     * @param string $attributeType
     * @param string $attributeCode
     * @param array $attributeValue
     * @param string $expected
     *
     * @return void
     * @dataProvider dataMultiAttribute
     */
    public function testSaveWithMultiAttribute(
        string $attributeType,
        string $attributeCode,
        array $attributeValue,
        string $expected
    ): void {
        $orderAddress = $this->getMockBuilder(OrderAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId', 'hasData', 'getData', 'setData'])
            ->getMock();

        $orderAddress->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['save']
        );
        $mapper->method('save')
            ->with($orderAddress);
        $this->metadata->method('getMapper')
            ->willReturn($mapper);

        $attributeModel = $this->getMockBuilder(Attribute::class)
            ->setMethods(['getFrontendInput', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeModel->method('getFrontendInput')->willReturn($attributeType);
        $attributeModel->method('getAttributeCode')->willReturn($attributeCode);
        $this->attributesList = [$attributeModel];

        $this->subject = $this->objectManager->getObject(
            AddressRepository::class,
            [
                'metadata' => $this->metadata,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionProcessor' => $this->collectionProcessorMock,
                'attributeMetadataDataProvider' => $this->attributeMetadataDataProvider,
                'attributesList' => $this->attributesList,
            ]
        );

        $orderAddress->method('hasData')->with($attributeCode)->willReturn(true);
        $orderAddress->method('getData')->with($attributeCode)->willReturn($attributeValue);
        $orderAddress->expects($this->once())->method('setData')->with($attributeCode, $expected);

        $this->assertEquals($orderAddress, $this->subject->save($orderAddress));
    }

    /**
     * Data for testSaveWithMultiAttribute
     *
     * @return array
     */
    public function dataMultiAttribute(): array
    {
        $data = [
            'multiselect' => [
                'multiselect',
                'attr_multiselect',
                [
                    'opt1',
                    'opt2',
                ],
                'opt1,opt2',
            ],
            'multiline' => [
                'multiline',
                'attr_multiline',
                [
                    'line1',
                    'line2',
                ],
                'line1'.PHP_EOL.'line2',
            ],
        ];

        return $data;
    }
}
