<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Export\Config;

use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Export\Config\Converter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var Manager|MockObject
     */
    protected $moduleManager;

    protected function setUp(): void
    {
        $this->filePath = realpath(__DIR__) . '/_files/';
        $this->moduleManager = $this->createPartialMock(Manager::class, ['isOutputEnabled']);
        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Converter::class,
            [
                'moduleManager' => $this->moduleManager
            ]
        );
    }

    public function testConvert()
    {
        $testDom = $this->filePath . 'export.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $expectedArray = include $this->filePath . 'export.php';
        $this->moduleManager->expects($this->any())->method('isOutputEnabled')->willReturn(true);
        $this->assertEquals($expectedArray, $this->model->convert($dom));
    }

    public function testConvertWithDisabledModules()
    {
        $testDom = $this->filePath . 'export.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $notExpectedArray = include $this->filePath . 'export.php';
        $this->moduleManager->expects($this->any())->method('isOutputEnabled')->willReturn(false);
        $this->assertNotEquals($notExpectedArray, $this->model->convert($dom));
    }
}
