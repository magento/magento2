<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Widget\Model\Config;

use Magento\TestFramework\Helper\Bootstrap;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Reader
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileResolver;

    public function setUp()
    {
        $this->fileResolver = $this->getMockForAbstractClass('Magento\Framework\Config\FileResolverInterface');
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->create(
            'Magento\Widget\Model\Config\Reader', ['fileResolver' => $this->fileResolver]
        );
    }

    public function testRead()
    {
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->willReturn([file_get_contents(__DIR__ . '/_files/orders_and_returns.xml')]);
        $expected = include __DIR__ . '/_files/expectedGlobalArray.php';
        $this->assertEquals($expected, $this->model->read('global'));
    }

    public function testReadFile()
    {
        $file = file_get_contents(__DIR__ . '/_files/orders_and_returns.xml');
        $expected = include __DIR__ . '/_files/expectedGlobalArray.php';
        $this->assertEquals($expected, $this->model->readFile($file));
    }

    public function testMergeCompleteAndPartial()
    {
        $fileList = [
            file_get_contents(__DIR__ . '/_files/catalog_new_products_list.xml'),
            file_get_contents(__DIR__ . '/_files/orders_and_returns_customized.xml'),
        ];
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('widget.xml', 'global')
            ->willReturn($fileList);
        $expected = include __DIR__ . '/_files/expectedMergedArray.php';
        $this->assertEquals($expected, $this->model->read('global'));
    }
}
