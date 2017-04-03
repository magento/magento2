<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Config;

use Magento\Analytics\Model\Config\Mapper;
use Magento\Analytics\Model\Config\Reader;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapperMock;

    /**
     * @var ReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerXmlMock;

    /**
     * @var ReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerDbMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->mapperMock = $this->getMockBuilder(Mapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->readerXmlMock = $this->getMockBuilder(ReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->readerDbMock = $this->getMockBuilder(ReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->reader = $this->objectManagerHelper->getObject(
            Reader::class,
            [
                'mapper' => $this->mapperMock,
                'readers' => [
                    $this->readerXmlMock,
                    $this->readerDbMock,
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function testRead()
    {
        $scope = 'store';
        $xmlReaderResult = [
            'config' => ['node1' => ['node2' => 'node4']]
        ];
        $dbReaderResult = [
            'config' => ['node1' => ['node2' => 'node3']]
        ];
        $mapperResult = ['node2' => ['node3', 'node4']];

        $this->readerXmlMock
            ->expects($this->once())
            ->method('read')
            ->with($scope)
            ->willReturn($xmlReaderResult);

        $this->readerDbMock
            ->expects($this->once())
            ->method('read')
            ->with($scope)
            ->willReturn($dbReaderResult);

        $this->mapperMock
            ->expects($this->once())
            ->method('execute')
            ->with(array_merge_recursive($xmlReaderResult, $dbReaderResult))
            ->willReturn($mapperResult);

        $this->assertSame($mapperResult, $this->reader->read($scope));
    }
}
