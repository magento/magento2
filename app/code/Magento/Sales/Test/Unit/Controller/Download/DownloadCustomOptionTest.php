<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Download;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Unserialize\Unserialize;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Sales\Controller\Download\DownloadCustomOption;
use Magento\Sales\Model\Download;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DownloadCustomOptionTest extends TestCase
{
    /**
     * Option ID Test Value
     */
    const OPTION_ID = '123456';

    /**
     * Option Code Test Value
     */
    const OPTION_CODE = 'option_123456';

    /**
     * Option Product ID Value
     */
    const OPTION_PRODUCT_ID = 'option_test_product_id';

    /**
     * Option Type Value
     */
    const OPTION_TYPE = 'file';

    /**
     * Option Value Test Value
     */
    const OPTION_VALUE = 'option_test_value';

    /**
     * Option Value Test Value
     */
    const SECRET_KEY = 'secret_key';

    /**
     * @var \Magento\Quote\Model\Quote\Item\Option|MockObject
     */
    protected $itemOptionMock;

    /**
     * @var \Magento\Catalog\Model\Product\Option|MockObject
     */
    protected $productOptionMock;

    /**
     * @var Unserialize|MockObject
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

    protected function setUp(): void
    {
        $resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->setMethods(['forward'])
            ->getMock();
        $resultForwardFactoryMock->expects($this->any())->method('create')->willReturn($this->resultForwardMock);

        $this->downloadMock = $this->getMockBuilder(Download::class)
            ->disableOriginalConstructor()
            ->setMethods(['downloadFile'])
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();
        $requestMock->expects($this->any())->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, self::OPTION_ID],
                    ['key', null, self::SECRET_KEY],
                ]
            );

        $this->itemOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getCode', 'getProductId', 'getValue'])
            ->getMock();

        $this->productOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getProductId', 'getType'])
            ->getMock();

        $objectManagerMock = $this->getMockBuilder(Download::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManagerMock->expects($this->any())->method('create')
            ->willReturnMap(
                [
                    [Option::class, $this->itemOptionMock],
                    [\Magento\Catalog\Model\Product\Option::class, $this->productOptionMock],
                ]
            );

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRequest',
                    'getObjectManager',
                ]
            )
            ->getMock();
        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($objectManagerMock);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($requestMock);

        $this->objectMock = $this->getMockBuilder(DownloadCustomOption::class)
            ->setMethods(['endExecute'])
            ->setConstructorArgs(
                [
                    'context'              => $contextMock,
                    'resultForwardFactory' => $resultForwardFactoryMock,
                    'download'             => $this->downloadMock,
                    'unserialize'          => $this->createMock(Unserialize::class),
                    'serializer'           => $this->serializerMock
                ]
            )
            ->getMock();
    }

    /**
     * @param array $itemOptionValues
     * @param array $productOptionValues
     * @param bool $noRouteOccurs
     * @dataProvider executeDataProvider
     */
    public function testExecute($itemOptionValues, $productOptionValues, $noRouteOccurs)
    {
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
            $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturn(true);
        } else {
            $unserializeResult = [self::SECRET_KEY => self::SECRET_KEY];

            $this->serializerMock->expects($this->once())
                ->method('unserialize')
                ->with($itemOptionValues[self::OPTION_VALUE])
                ->willReturn($unserializeResult);

            $this->downloadMock->expects($this->once())
                ->method('downloadFile')
                ->with($unserializeResult)
                ->willReturn(true);

            $this->objectMock->expects($this->once())->method('endExecute')->willReturn(true);
        }
        $this->objectMock->execute();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
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
