<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorageDatabase;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Model\Product\Option\UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rootDirectory = $this->getMockBuilder(ReadInterface::class)
            ->getMock();

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA, DriverPool::FILE)
            ->willReturn($this->rootDirectory);

        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreFileStorageDatabase = $this->getMock(
            \Magento\MediaStorage\Helper\File\Storage\Database::class,
            ['copyFile'],
            [],
            '',
            false
        );
    }

    /**
     * @return \Magento\Catalog\Model\Product\Option\Type\File
     */
    protected function getFileObject()
    {
        return $this->objectManager->getObject(
            \Magento\Catalog\Model\Product\Option\Type\File::class,
            [
                'filesystem' => $this->filesystemMock,
                'coreFileStorageDatabase' => $this->coreFileStorageDatabase,
                'serializer' => $this->serializer,
                'urlBuilder' => $this->urlBuilder,
                'escaper' => $this->escaper
            ]
        );
    }

    public function testGetCustomizedView()
    {
        $fileObject = $this->getFileObject();
        $optionInfo = ['option_value' => 'some serialized data'];

        $dataAfterSerialize = ['some' => 'array'];

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with('some serialized data')
            ->willReturn($dataAfterSerialize);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->willReturn('someUrl');

        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturn('string');

        $this->assertEquals(
            '<a href="someUrl" target="_blank">string</a> ',
            $fileObject->getCustomizedView($optionInfo)
        );
    }

    public function testCopyQuoteToOrder()
    {
        $optionMock = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();

        $quotePath = '/quote/path/path/uploaded.file';
        $orderPath = '/order/path/path/uploaded.file';

        $quoteValue = "{\"quote_path\":\"$quotePath\",\"order_path\":\"$orderPath\"}";

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($quoteValue)
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $optionMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($quoteValue));

        $this->rootDirectory->expects($this->any())
            ->method('isFile')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->rootDirectory->expects($this->any())
            ->method('isReadable')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->rootDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('/file.path'));

        $this->coreFileStorageDatabase->expects($this->any())
            ->method('copyFile')
            ->will($this->returnValue('true'));

        $fileObject = $this->getFileObject();
        $fileObject->setData('configuration_item_option', $optionMock);

        $this->assertInstanceOf(
            \Magento\Catalog\Model\Product\Option\Type\File::class,
            $fileObject->copyQuoteToOrder()
        );
    }

    public function testGetFormattedOptionValue()
    {
        $resultValue = ['result'];
        $optionValue = json_encode($resultValue);
        $urlParameter = 'parameter';

        $fileObject = $this->getFileObject();
        $fileObject->setCustomOptionUrlParams($urlParameter);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($optionValue)
            ->willReturn($resultValue);

        $resultValue['url'] = [
            'route' => 'sales/download/downloadCustomOption',
            'params' => $fileObject->getCustomOptionUrlParams()
        ];

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($resultValue)
            ->willReturn(json_encode($resultValue));

        $option = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['setValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $option->expects($this->once())
            ->method('setValue')
            ->with(json_encode($resultValue));

        $fileObject->setConfigurationItemOption($option);

        $fileObject->getFormattedOptionValue($optionValue);
    }

    public function testPrepareOptionValueForRequest()
    {
        $optionValue = 'string';
        $resultValue = ['result'];
        $fileObject = $this->getFileObject();

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($optionValue)
            ->willReturn($resultValue);

        $this->assertEquals($resultValue, $fileObject->prepareOptionValueForRequest($optionValue));
    }
}

