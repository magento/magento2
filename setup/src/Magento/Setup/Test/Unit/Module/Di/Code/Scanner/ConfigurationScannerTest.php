<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\Config\FileResolver;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurationScannerTest extends TestCase
{
    /**
     * @var FileResolver|MockObject
     */
    private $fileResolverMock;

    /**
     * @var AreaList|MockObject
     */
    private $areaListMock;

    /**
     * @var ConfigurationScanner
     */
    private $model;

    protected function setUp(): void
    {
        $this->fileResolverMock = $this->getMockBuilder(FileResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->areaListMock = $this->getMockBuilder(AreaList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            ConfigurationScanner::class,
            [
                'fileResolver' => $this->fileResolverMock,
                'areaList' => $this->areaListMock,
            ]
        );
    }

    public function testScan()
    {
        $codes = ['code1', 'code2'];
        $iteratorMock = $this->getMockBuilder(FileIterator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->areaListMock->expects($this->once())
            ->method('getCodes')
            ->willReturn($codes);
        $counts = count($codes) + 2;
        $this->fileResolverMock->expects($this->exactly($counts))
            ->method('get')
            ->willReturn($iteratorMock);
        $files = ['file1' => 'onefile', 'file2' => 'anotherfile'];
        $iteratorMock->expects($this->exactly($counts))
            ->method('toArray')
            ->willReturn($files);
        $this->assertEquals(array_keys($files), $this->model->scan('di.xml'));
    }
}
