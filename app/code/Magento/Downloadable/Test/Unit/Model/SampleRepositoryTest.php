<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Downloadable\Api\Data\File\ContentUploaderInterface;
use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Product\TypeHandler\Sample;
use Magento\Downloadable\Model\Sample\ContentValidator;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Downloadable\Model\SampleRepository;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Json\EncoderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SampleRepositoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $repositoryMock;

    /**
     * @var MockObject
     */
    protected $productTypeMock;

    /**
     * @var MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var MockObject
     */
    protected $contentUploaderMock;

    /**
     * @var MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var MockObject
     */
    protected $sampleFactoryMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var SampleRepository
     */
    protected $service;

    /**
     * @var MockObject
     */
    protected $sampleDataObjectFactory;

    /**
     * @var MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var MockObject
     */
    protected $sampleHandlerMock;

    /**
     * @var MockObject
     */
    protected $entityMetadataMock;

    protected function setUp(): void
    {
        $this->productMock = $this->createPartialMock(
            Product::class,
            ['__wakeup', 'getTypeId', 'setDownloadableData', 'save', 'getId', 'getStoreId', 'getData']
        );
        $this->repositoryMock = $this->createMock(ProductRepository::class);
        $this->productTypeMock = $this->createMock(Type::class);
        $this->contentValidatorMock = $this->createMock(ContentValidator::class);
        $this->contentUploaderMock = $this->createMock(
            ContentUploaderInterface::class
        );
        $this->jsonEncoderMock = $this->createMock(
            EncoderInterface::class
        );
        $this->sampleFactoryMock = $this->createPartialMock(
            SampleFactory::class,
            ['create']
        );
        $this->productTypeMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sampleDataObjectFactory = $this->getMockBuilder(
            SampleInterfaceFactory::class
        )->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new SampleRepository(
            $this->repositoryMock,
            $this->productTypeMock,
            $this->sampleDataObjectFactory,
            $this->contentValidatorMock,
            $this->contentUploaderMock,
            $this->jsonEncoderMock,
            $this->sampleFactoryMock
        );

        $this->entityMetadataMock = $this->getMockBuilder(
            EntityMetadataInterface::class
        )->getMockForAbstractClass();
        $linkRepository = new \ReflectionClass(get_class($this->service));
        $metadataPoolProperty = $linkRepository->getProperty('metadataPool');
        $this->metadataPoolMock = $this->getMockBuilder(
            MetadataPool::class
        )->disableOriginalConstructor()->getMock();
        $metadataPoolProperty->setAccessible(true);
        $metadataPoolProperty->setValue(
            $this->service,
            $this->metadataPoolMock
        );
        $saveHandlerProperty = $linkRepository->getProperty('sampleTypeHandler');
        $this->sampleHandlerMock = $this->getMockBuilder(
            Sample::class
        )->disableOriginalConstructor()->getMock();
        $saveHandlerProperty->setAccessible(true);
        $saveHandlerProperty->setValue(
            $this->service,
            $this->sampleHandlerMock
        );

        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($this->entityMetadataMock);
    }

    /**
     * @param array $sampleData
     * @return MockObject
     */
    protected function getSampleMock(array $sampleData)
    {
        $sampleMock = $this->createMock(SampleInterface::class);

        if (isset($sampleData['id'])) {
            $sampleMock->expects($this->any())->method('getId')->willReturn($sampleData['id']);
        }
        $sampleMock->expects($this->any())->method('getTitle')->will($this->returnValue($sampleData['title']));
        $sampleMock->expects($this->any())->method('getSortOrder')->will(
            $this->returnValue($sampleData['sort_order'])
        );

        if (isset($sampleData['sample_type'])) {
            $sampleMock->expects($this->any())->method('getSampleType')->will(
                $this->returnValue($sampleData['sample_type'])
            );
        }
        if (isset($sampleData['sample_url'])) {
            $sampleMock->expects($this->any())->method('getSampleUrl')->will(
                $this->returnValue($sampleData['sample_url'])
            );
        }
        if (isset($sampleData['sample_file'])) {
            $sampleMock->expects($this->any())->method('getSampleFile')->will(
                $this->returnValue($sampleData['sample_file'])
            );
        }

        return $sampleMock;
    }

    public function testCreate()
    {
        $productSku = 'simple';
        $sampleData = [
            'title' => 'Title',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://example.com/',
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleMock)
            ->will($this->returnValue(true));

        $this->sampleHandlerMock->expects($this->once())->method('save')->with(
            $this->productMock,
            [
                'sample' => [
                    [
                        'sample_id' => 0,
                        'is_delete' => 0,
                        'type' => $sampleData['sample_type'],
                        'sort_order' => $sampleData['sort_order'],
                        'title' => $sampleData['title'],
                        'sample_url' => $sampleData['sample_url'],
                    ],
                ],
            ]
        );
        $this->service->save($productSku, $sampleMock);
    }

    public function testCreateThrowsExceptionIfTitleIsEmpty()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The sample title is empty. Enter the title and try again.');
        $productSku = 'simple';
        $sampleData = [
            'title' => '',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://example.com/',
        ];

        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleMock)
            ->will($this->returnValue(true));

        $this->sampleHandlerMock->expects($this->never())->method('save');

        $this->service->save($productSku, $sampleMock);
    }

    public function testUpdate()
    {
        $sampleId = 1;
        $productId = 1;
        $productSku = 'simple';
        $sampleData = [
            'id' => $sampleId,
            'title' => 'Updated Title',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://example.com/',
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')->will($this->returnValue($productId));
        $existingSampleMock = $this->createPartialMock(
            \Magento\Downloadable\Model\Sample::class,
            ['__wakeup', 'getId', 'load', 'getProductId']
        );
        $this->sampleFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($existingSampleMock));
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleMock)
            ->will($this->returnValue(true));

        $existingSampleMock->expects($this->any())->method('getId')->will($this->returnValue($sampleId));
        $existingSampleMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $existingSampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());

        $this->sampleHandlerMock->expects($this->once())->method('save')->with(
            $this->productMock,
            [
                'sample' => [
                    [
                        'sample_id' => $sampleId,
                        'is_delete' => 0,
                        'type' => $sampleData['sample_type'],
                        'sort_order' => $sampleData['sort_order'],
                        'title' => $sampleData['title'],
                        'sample_url' => $sampleData['sample_url'],
                    ],
                ],
            ]
        );

        $this->assertEquals($sampleId, $this->service->save($productSku, $sampleMock));
    }

    public function testUpdateWithExistingFile()
    {
        $sampleId = 1;
        $productId = 1;
        $productSku = 'simple';
        $sampleFile = '/s/a/sample.jpg';
        $encodedFile = 'something';
        $sampleData = [
            'id' => $sampleId,
            'title' => 'Updated Title',
            'sort_order' => 1,
            'sample_type' => 'file',
            'sample_file' => $sampleFile,
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')->will($this->returnValue($productId));
        $existingSampleMock = $this->createPartialMock(
            \Magento\Downloadable\Model\Sample::class,
            ['__wakeup', 'getId', 'load', 'getProductId']
        );
        $this->sampleFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($existingSampleMock));
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleMock)
            ->will($this->returnValue(true));

        $existingSampleMock->expects($this->any())->method('getId')->will($this->returnValue($sampleId));
        $existingSampleMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $existingSampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with(
                [
                    [
                        'file' => $sampleFile,
                        'status' => 'old',
                    ]
                ]
            )->willReturn($encodedFile);

        $this->sampleHandlerMock->expects($this->once())->method('save')->with(
            $this->productMock,
            [
                'sample' => [
                    [
                        'sample_id' => $sampleId,
                        'is_delete' => 0,
                        'type' => $sampleData['sample_type'],
                        'sort_order' => $sampleData['sort_order'],
                        'title' => $sampleData['title'],
                        'file' => $encodedFile,
                    ],
                ],
            ]
        );

        $this->assertEquals($sampleId, $this->service->save($productSku, $sampleMock));
    }

    public function testUpdateThrowsExceptionIfTitleIsEmptyAndScopeIsGlobal()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The sample title is empty. Enter the title and try again.');
        $sampleId = 1;
        $productSku = 'simple';
        $productId = 1;
        $sampleData = [
            'id' => $sampleId,
            'title' => '',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'https://google.com',
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')->will($this->returnValue($productId));
        $existingSampleMock = $this->createPartialMock(
            \Magento\Downloadable\Model\Sample::class,
            ['__wakeup', 'getId', 'load', 'save', 'getProductId']
        );
        $existingSampleMock->expects($this->any())->method('getId')->will($this->returnValue($sampleId));
        $existingSampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());
        $existingSampleMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $this->sampleFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($existingSampleMock));
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleMock)
            ->will($this->returnValue(true));

        $this->sampleHandlerMock->expects($this->never())->method('save');

        $this->service->save($productSku, $sampleMock, true);
    }

    public function testDelete()
    {
        $sampleId = 1;
        $sampleMock = $this->createMock(\Magento\Downloadable\Model\Sample::class);
        $this->sampleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sampleMock));
        $sampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());
        $sampleMock->expects($this->any())->method('getId')->will($this->returnValue($sampleId));
        $sampleMock->expects($this->once())->method('delete');

        $this->assertTrue($this->service->delete($sampleId));
    }

    public function testDeleteThrowsExceptionIfSampleIdIsNotValid()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage(
            'No downloadable sample with the provided ID was found. Verify the ID and try again.'
        );
        $sampleId = 1;
        $sampleMock = $this->createMock(\Magento\Downloadable\Model\Sample::class);
        $this->sampleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sampleMock));
        $sampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());
        $sampleMock->expects($this->once())->method('getId');
        $sampleMock->expects($this->never())->method('delete');

        $this->service->delete($sampleId);
    }

    public function testGetList()
    {
        $productSku = 'downloadable_sku';

        $sampleData = [
            'id' => 324,
            'store_title' => 'rock melody sample',
            'title' => 'just melody sample',
            'sort_order' => 21,
            'sample_type' => 'file',
            'sample_url' => null,
            'sample_file' => '/r/o/rock.melody.ogg',
        ];

        $sampleMock = $this->createPartialMock(
            \Magento\Downloadable\Model\Sample::class,
            [
                'getId',
                'getStoreTitle',
                'getTitle',
                'getSampleType',
                'getSampleFile',
                'getSampleUrl',
                'getSortOrder',
                'getData',
                '__wakeup',
            ]
        );

        $sampleInterfaceMock = $this->createMock(SampleInterface::class);

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->will($this->returnValue($this->productMock));

        $this->productTypeMock->expects($this->once())
            ->method('getSamples')
            ->with($this->productMock)
            ->will($this->returnValue([$sampleMock]));

        $this->setSampleAssertions($sampleMock, $sampleData);

        $this->sampleDataObjectFactory->expects($this->once())->method('create')->willReturn($sampleInterfaceMock);

        $this->assertEquals([$sampleInterfaceMock], $this->service->getList($productSku));
    }

    /**
     * @param $resource
     * @param $inputData
     */
    protected function setSampleAssertions($resource, $inputData)
    {
        $resource->expects($this->any())->method('getId')->will($this->returnValue($inputData['id']));
        $resource->expects($this->any())->method('getStoreTitle')
            ->will($this->returnValue($inputData['store_title']));
        $resource->expects($this->any())->method('getTitle')
            ->will($this->returnValue($inputData['title']));
        $resource->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue($inputData['sort_order']));
        $resource->expects($this->any())->method('getSampleType')
            ->will($this->returnValue($inputData['sample_type']));
        $resource->expects($this->any())->method('getSampleFile')
            ->will($this->returnValue($inputData['sample_file']));
        $resource->expects($this->any())->method('getSampleUrl')
            ->will($this->returnValue($inputData['sample_url']));
    }
}
