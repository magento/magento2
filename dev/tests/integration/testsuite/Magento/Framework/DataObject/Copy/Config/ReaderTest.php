<?php
/**
 * \Magento\Framework\DataObject\Copy\Config\Reader
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Copy\Config;

use Magento\TestFramework\Helper\Bootstrap;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Copy\Config\Reader
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileResolver;

    public function setUp()
    {
        $this->fileResolver = $this->getMockForAbstractClass(\Magento\Framework\Config\FileResolverInterface::class);
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->create(
            \Magento\Framework\DataObject\Copy\Config\Reader::class,
            ['fileResolver' => $this->fileResolver]
        );
    }

    public function testRead()
    {
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('fieldset.xml', 'global')
            ->willReturn([file_get_contents(__DIR__ . '/_files/fieldset.xml')]);
        $expected = include __DIR__ . '/_files/expectedArray.php';
        $this->assertEquals($expected, $this->model->read('global'));
    }

    public function testMergeCompleteAndPartial()
    {
        $fileList = [
            file_get_contents(__DIR__ . '/_files/partialFieldsetFirst.xml'),
            file_get_contents(__DIR__ . '/_files/partialFieldsetSecond.xml'),
        ];
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('fieldset.xml', 'global')
            ->willReturn($fileList);
        $expected = [
            'global' => [
                'quote_convert_item' => [
                    'event_id' => ['to_order_item' => "*"],
                    'event_name' => ['to_order_item' => "*"],
                    'event_description' => ['to_order_item' => "complexDescription"],
                ],
            ],
        ];
        $this->assertEquals($expected, $this->model->read('global'));
    }
}
