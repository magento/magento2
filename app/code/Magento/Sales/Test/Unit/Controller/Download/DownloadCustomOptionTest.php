<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Download;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Unserialize\Unserialize;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Sales\Controller\Download\DownloadCustomOption;
use Magento\Sales\Model\Download;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadCustomOptionTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Option ID Test Value
     */
    public const OPTION_ID = '123456';

    /**
     * Option Code Test Value
     */
    public const OPTION_CODE = 'option_123456';

    /**
     * Option Product ID Value
     */
    public const OPTION_PRODUCT_ID = 'option_test_product_id';

    /**
     * Option Type Value
     */
    public const OPTION_TYPE = 'file';

    /**
     * Option Value Test Value
     */
    public const OPTION_VALUE = 'option_test_value';

    /**
     * Option Value Test Value
     */
    public const SECRET_KEY = 'secret_key';

    /**
     * @var \Magento\Quote\Model\Quote\Item\Option|MockObject
     */
    protected $itemOptionMock;

    /**
     * @var \Magento\Catalog\Model\Product\Option|MockObject
     */
    protected $productOptionMock;

    /**
     * @var Json|MockObject
     */
    protected $serializerMock;

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * @var Download|MockObject
     */
    protected $downloadMock;

    /**
     * @var DownloadCustomOption|MockObject
     */
    protected $objectMock;

    /**
     * @var array
     */
    private $requestParams = ['id' => self::OPTION_ID, 'key' => self::SECRET_KEY];

    /**
     * @var CollectionFactory|MockObject
     */
    private $itemCollectionFactoryMock;

    /**
     * @var OrderCollectionFactory|MockObject
     */
    private $orderCollectionFactoryMock;

    protected function setUp(): void
    {
        $resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forward'])
            ->getMock();
        $resultForwardFactoryMock->expects($this->any())->method('create')->willReturn($this->resultForwardMock);

        $this->downloadMock = $this->getMockBuilder(Download::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createResponse'])
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['serialize', 'unserialize'])
            ->getMock();

        $requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParam'])
            ->getMock();
        $requestMock->method('getParam')->willReturnCallback(
            fn ($name, $default = null) => $this->requestParams[$name] ?? $default
        );

        $this->itemOptionMock = $this->createPartialMockWithReflection(
            Option::class,
            ['getCode', 'getProductId', 'load', 'getId', 'getValue']
        );

        $this->productOptionMock = $this->createPartialMockWithReflection(
            ProductOption::class,
            ['getProductId', 'load', 'getId', 'getType']
        );

        $objectManagerMock = $this->createPartialMockWithReflection(Download::class, ['create']);
        $objectManagerMock->expects($this->any())->method('create')
            ->willReturnMap(
                [
                    [Option::class, $this->itemOptionMock],
                    [ProductOption::class, $this->productOptionMock],
                ]
            );

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getRequest',
                    'getObjectManager',
                ]
            )
            ->getMock();
        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($objectManagerMock);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($requestMock);

        $this->itemCollectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->orderCollectionFactoryMock = $this->createMock(OrderCollectionFactory::class);

        $this->objectMock = $this->getMockBuilder(DownloadCustomOption::class)
            ->onlyMethods(['endExecute'])
            ->setConstructorArgs(
                [
                    'context'              => $contextMock,
                    'resultForwardFactory' => $resultForwardFactoryMock,
                    'download'             => $this->downloadMock,
                    'unserialize'          => $this->createMock(Unserialize::class),
                    'serializer'           => $this->serializerMock,
                    'itemCollectionFactory' => $this->itemCollectionFactoryMock,
                    'orderCollectionFactory' => $this->orderCollectionFactoryMock
                ]
            )
            ->getMock();
    }

    /**
     * @param array $itemOptionValues
     * @param array $productOptionValues
     * @param bool $noRouteOccurs
     */
    #[DataProvider('executeDataProvider')]
    public function testExecute($itemOptionValues, $productOptionValues, $noRouteOccurs)
    {
        $this->itemCollectionFactoryMock->expects($this->never())->method('create');
        $this->orderCollectionFactoryMock->expects($this->never())->method('create');
        if (!empty($itemOptionValues)) {
            $this->itemOptionMock->expects($this->once())->method('load')->willReturnSelf();
            $this->itemOptionMock->expects($this->once())
                ->method('getId')
                ->willReturn($itemOptionValues[self::OPTION_ID]);
            $this->itemOptionMock->expects($this->any())
                ->method('getCode')
                ->willReturn($itemOptionValues[self::OPTION_CODE]);
            $this->itemOptionMock->expects($this->any())
                ->method('getProductId')
                ->willReturn($itemOptionValues[self::OPTION_PRODUCT_ID]);
            $this->itemOptionMock->expects($this->any())
                ->method('getValue')
                ->willReturn($itemOptionValues[self::OPTION_VALUE]);
        }
        if (!empty($productOptionValues)) {
            $this->productOptionMock->expects($this->once())->method('load')->willReturnSelf();
            $this->productOptionMock->expects($this->any())
                ->method('getId')
                ->willReturn($productOptionValues[self::OPTION_ID]);
            $this->productOptionMock->expects($this->any())
                ->method('getProductId')
                ->willReturn($productOptionValues[self::OPTION_PRODUCT_ID]);
            $this->productOptionMock->expects($this->any())
                ->method('getType')
                ->willReturn($productOptionValues[self::OPTION_TYPE]);
        }
        if ($noRouteOccurs) {
            $result = $this->resultForwardMock;
            $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturnSelf();
        } else {
            $unserializeResult = [self::SECRET_KEY => self::SECRET_KEY];

            $this->serializerMock->expects($this->once())
                ->method('unserialize')
                ->with($itemOptionValues[self::OPTION_VALUE])
                ->willReturn($unserializeResult);

            $result = $this->getMockBuilder(ResponseInterface::class)
                ->getMock();
            $this->downloadMock->expects($this->once())
                ->method('createResponse')
                ->with($unserializeResult)
                ->willReturn($result);

        }
        $this->assertSame($result, $this->objectMock->execute());
    }

    /**
     * @return array
     */
    public static function executeDataProvider()
    {
        return [
            [ //Good
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_CODE => self::OPTION_CODE,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_VALUE => self::OPTION_VALUE
                ],
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_TYPE => self::OPTION_TYPE,
                ],
                false
            ],
            [ //No Option ID
                [
                    self::OPTION_ID => false,
                    self::OPTION_CODE => self::OPTION_CODE,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_VALUE => self::OPTION_VALUE
                ],
                [],
                true
            ],
            [ //No Product Option
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_CODE => self::OPTION_CODE,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_VALUE => self::OPTION_VALUE
                ],
                [],
                true
            ],
            [ //No Product Option ID
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_CODE => self::OPTION_CODE,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_VALUE => self::OPTION_VALUE
                ],
                [
                    self::OPTION_ID => null,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_TYPE => self::OPTION_TYPE,
                ],
                true
            ],
            [ //Not Matching Product IDs in Inventory Option
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_CODE => self::OPTION_CODE,
                    self::OPTION_PRODUCT_ID => 'bad_test_product_ID',
                    self::OPTION_VALUE => self::OPTION_VALUE
                ],
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_TYPE => self::OPTION_TYPE,
                ],
                true
            ],
            [ //Not Matching Product IDs in Product Option
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_CODE => self::OPTION_CODE,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_VALUE => self::OPTION_VALUE
                ],
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_PRODUCT_ID => 'bad_test_product_ID',
                    self::OPTION_TYPE => self::OPTION_TYPE,
                ],
                true
            ],
            [ //Incorrect Option Type
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_CODE => self::OPTION_CODE,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_VALUE => self::OPTION_VALUE
                ],
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_TYPE => 'bad_test_option_type',
                ],
                true
            ],
        ];
    }

    /**
     * @param mixed $options
     * @param mixed $key
     * @param bool $success
     * @return void
     */
    #[DataProvider('orderOptionsProvider')]
    public function testExecuteAfterQuoteDeletion($options, $key, bool $success): void
    {
        $this->requestParams = [
            'id' => self::OPTION_ID,
            'quote' => '21',
            'item' => '42',
            'option' => self::OPTION_ID,
            'key' => $key,
        ];
        $this->itemOptionMock->method('load')->willReturnSelf();
        $this->itemOptionMock->method('getId')->willReturn(null);
        $item = $this->createStub(Item::class);
        $item->method('getProductOptionByCode')->willReturn($options);
        $otherItem = $this->createStub(Item::class);
        $otherItem->method('getProductOptionByCode')->willReturn([[
            'option_id' => self::OPTION_ID,
            'option_type' => 'file',
            'option_value' => '{"secret_key":"different-key"}',
        ]]);
        $this->configureOrderItems([$otherItem, $item]);
        $this->serializerMock->method('unserialize')->willReturnCallback(
            static fn ($value) => (new Json())->unserialize($value)
        );
        if ($success) {
            $result = $this->createStub(ResponseInterface::class);
            $this->downloadMock->expects($this->once())->method('createResponse')
                ->with(['secret_key' => self::SECRET_KEY])->willReturn($result);
            $this->resultForwardMock->expects($this->never())->method('forward');
        } else {
            $result = $this->resultForwardMock;
            $this->downloadMock->expects($this->never())->method('createResponse');
            $this->resultForwardMock->expects($this->once())->method('forward')
                ->with('noroute')->willReturnSelf();
        }
        $this->assertSame($result, $this->objectMock->execute());
    }

    /**
     * @param Item[] $items
     * @return void
     */
    private function configureOrderItems(array $items): void
    {
        $orders = $this->createMock(OrderCollection::class);
        $orders->expects($this->once())->method('addFieldToFilter')->with('quote_id', '21')->willReturnSelf();
        $orders->expects($this->once())->method('getAllIds')->willReturn([10, 11]);
        $this->orderCollectionFactoryMock->expects($this->once())->method('create')->willReturn($orders);
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->exactly(2))->method('addFieldToFilter')->willReturnMap([
            ['order_id', ['in' => [10, 11]], $collection],
            ['quote_item_id', '42', $collection],
        ]);
        $collection->method('getIterator')->willReturn(new \ArrayIterator($items));
        $this->itemCollectionFactoryMock->expects($this->once())->method('create')->willReturn($collection);
    }

    public function testFallbackWithoutMatchingOrders(): void
    {
        $this->requestParams = ['quote' => '21', 'item' => '42', 'option' => self::OPTION_ID];
        $this->itemOptionMock->method('load')->willReturnSelf();
        $this->itemOptionMock->method('getId')->willReturn(null);
        $orders = $this->createMock(OrderCollection::class);
        $orders->expects($this->once())->method('addFieldToFilter')->with('quote_id', '21')->willReturnSelf();
        $orders->expects($this->once())->method('getAllIds')->willReturn([]);
        $this->orderCollectionFactoryMock->expects($this->once())->method('create')->willReturn($orders);
        $this->itemCollectionFactoryMock->expects($this->never())->method('create');
        $this->downloadMock->expects($this->never())->method('createResponse');
        $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturnSelf();
        $this->assertSame($this->resultForwardMock, $this->objectMock->execute());
    }

    public function testFallbackWithoutMatchingOrderItems(): void
    {
        $this->requestParams = ['quote' => '21', 'item' => '42', 'option' => self::OPTION_ID];
        $this->itemOptionMock->method('load')->willReturnSelf();
        $this->itemOptionMock->method('getId')->willReturn(null);
        $this->configureOrderItems([]);
        $this->downloadMock->expects($this->never())->method('createResponse');
        $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturnSelf();
        $this->assertSame($this->resultForwardMock, $this->objectMock->execute());
    }

    /**
     * @return array
     */
    public static function orderOptionsProvider(): array
    {
        $file = [
            'option_id' => self::OPTION_ID,
            'option_type' => 'file',
            'option_value' => '{"secret_key":"secret_key"}',
        ];
        return [
            'file' => [[$file], self::SECRET_KEY, true],
            'associative' => [['upload' => $file], self::SECRET_KEY, true],
            'sparse' => [[7 => $file], self::SECRET_KEY, true],
            'wrong key' => [[$file], 'wrong', false],
            'missing key' => [[$file], null, false],
            'array key' => [[$file], [self::SECRET_KEY], false],
            'absent options or order item' => [null, self::SECRET_KEY, false],
            'scalar options' => ['invalid', self::SECRET_KEY, false],
            'empty options' => [[], self::SECRET_KEY, false],
            'malformed entry' => [['invalid'], self::SECRET_KEY, false],
            'missing option' => [[array_replace($file, ['option_id' => '99'])], self::SECRET_KEY, false],
            'non-file' => [[array_replace($file, ['option_type' => 'field'])], self::SECRET_KEY, false],
            'missing type' => [[['option_id' => self::OPTION_ID]], self::SECRET_KEY, false],
            'array option ID' => [[array_replace($file, ['option_id' => []])], self::SECRET_KEY, false],
            'array value' => [[array_replace($file, ['option_value' => []])], self::SECRET_KEY, false],
            'invalid json' => [[array_replace($file, ['option_value' => '{'])], self::SECRET_KEY, false],
            'scalar json' => [[array_replace($file, ['option_value' => '42'])], self::SECRET_KEY, false],
            'missing stored key' => [[array_replace($file, ['option_value' => '{}'])], null, false],
            'empty stored key' => [[array_replace($file, ['option_value' => '{"secret_key":""}'])], '', false],
        ];
    }

    /**
     * @param array $params
     * @return void
     */
    #[DataProvider('missingIdentifiersProvider')]
    public function testDeletedQuoteWithoutValidIdentifiers(array $params): void
    {
        $this->requestParams = $params;
        $this->itemOptionMock->method('load')->willReturnSelf();
        $this->itemOptionMock->method('getId')->willReturn(null);
        $this->itemCollectionFactoryMock->expects($this->never())->method('create');
        $this->orderCollectionFactoryMock->expects($this->never())->method('create');
        $this->downloadMock->expects($this->never())->method('createResponse');
        $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturnSelf();
        $this->assertSame($this->resultForwardMock, $this->objectMock->execute());
    }

    /**
     * @return array
     */
    public static function missingIdentifiersProvider(): array
    {
        return [
            'legacy URL' => [['id' => self::OPTION_ID, 'key' => self::SECRET_KEY]],
            'missing option' => [['quote' => '21', 'item' => '42']],
            'missing quote' => [['item' => '42', 'option' => self::OPTION_ID]],
            'array quote' => [['quote' => ['21'], 'item' => '42', 'option' => self::OPTION_ID]],
            'invalid item' => [['quote' => '21', 'item' => 'bad', 'option' => self::OPTION_ID]],
            'array item' => [['quote' => '21', 'item' => ['42'], 'option' => self::OPTION_ID]],
            'array option' => [['quote' => '21', 'item' => '42', 'option' => [self::OPTION_ID]]],
        ];
    }

    /**
     * @param string $code
     * @return void
     */
    #[DataProvider('invalidQuoteCodesProvider')]
    public function testInvalidQuoteOptionCode(string $code): void
    {
        $this->itemOptionMock->method('load')->willReturnSelf();
        $this->itemOptionMock->method('getId')->willReturn(self::OPTION_ID);
        $this->itemOptionMock->method('getCode')->willReturn($code);
        $this->productOptionMock->expects($this->never())->method('load');
        $this->itemCollectionFactoryMock->expects($this->never())->method('create');
        $this->orderCollectionFactoryMock->expects($this->never())->method('create');
        $this->downloadMock->expects($this->never())->method('createResponse');
        $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturnSelf();
        $this->assertSame($this->resultForwardMock, $this->objectMock->execute());
    }

    /**
     * @return array
     */
    public static function invalidQuoteCodesProvider(): array
    {
        return [[''], ['info_buyRequest'], ['option_invalid']];
    }

    public function testExecuteBadSecretKey()
    {
        $this->itemOptionMock->expects($this->once())->method('load')->willReturnSelf();
        $this->itemOptionMock->expects($this->once())->method('getId')->willReturn(self::OPTION_ID);
        $this->itemOptionMock->expects($this->any())->method('getCode')->willReturn(self::OPTION_CODE);
        $this->itemOptionMock->expects($this->any())->method('getProductId')->willReturn(self::OPTION_PRODUCT_ID);
        $this->itemOptionMock->expects($this->any())->method('getValue')->willReturn(self::OPTION_VALUE);

        $this->productOptionMock->expects($this->once())->method('load')->willReturnSelf();
        $this->productOptionMock->expects($this->any())->method('getId')->willReturn(self::OPTION_ID);
        $this->productOptionMock->expects($this->any())->method('getProductId')->willReturn(self::OPTION_PRODUCT_ID);
        $this->productOptionMock->expects($this->any())->method('getType')->willReturn(self::OPTION_TYPE);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(self::OPTION_VALUE)
            ->willReturn([self::SECRET_KEY => 'bad_test_secret_key']);

        $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturn(true);

        $this->objectMock->execute();
    }
}
