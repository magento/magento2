<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogImportExport\Model\Import\Product\Validator\Media */
    protected $media;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->media = $this->objectManagerHelper->getObject(
            'Magento\CatalogImportExport\Model\Import\Product\Validator\Media',
            [
                
            ]
        );
    }

    public function testInit()
    {
        $result = $this->media->init(null);
        $this->assertEquals($this->media, $result);
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider isMediaValidDataProvider
     */
    public function testIsValid($data, $expected)
    {
        $result = $this->media->isValid($data);
        $this->assertEquals($expected['result'], $result);
        $messages = $this->media->getMessages();
        $this->assertEquals($expected['messages'], $messages);
    }

    public function testIsValidClearMessagesCall()
    {
        $media = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\Validator\Media',
            ['_clearMessages'],
            [],
            '',
            false
        );
        $media->expects($this->once())->method('_clearMessages');

        $media->isValid([]);
    }

    /**
     * @return array
     */
    public function isMediaValidDataProvider()
    {
        return [
            'valid' => [
                ['_media_image' => 1, '_media_attribute_id' => 1],
                ['result' => true, 'messages' => []],
            ],
            'valid2' => [
                ['_media_attribute_id' => 1],
                ['result' => true, 'messages' => []],
            ],
            'invalid' => [
                ['_media_image' => 1],
                ['result' => true,'messages' => []],
            ]
        ];
    }
}
