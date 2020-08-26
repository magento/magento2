<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Media;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Url\Validator;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    /** @var Media */
    protected $media;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var Validator|MockObject
     */
    private $validatorMock;

    protected function setUp(): void
    {
        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getMultipleValueSeparator')
            ->willReturn(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR);
        $contextMock->expects($this->any())
            ->method('retrieveMessageTemplate')
            ->with(Media::ERROR_INVALID_MEDIA_URL_OR_PATH)
            ->willReturn('%s');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->media = $this->objectManagerHelper->getObject(
            Media::class,
            [
                'validator' => $this->validatorMock
            ]
        );
        $this->media->init($contextMock);
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
        $this->validatorMock->expects($this->never())
            ->method('isValid');

        $result = $this->media->isValid($data);
        $this->assertEquals($expected['result'], $result);
        $messages = $this->media->getMessages();
        $this->assertEquals($expected['messages'], $messages);
    }

    public function testIsValidClearMessagesCall()
    {
        $media = $this->createPartialMock(Media::class, ['_clearMessages']);
        $media->expects($this->once())->method('_clearMessages');

        $media->isValid([]);
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider isValidAdditionalImagesPathDataProvider
     */
    public function testIsValidAdditionalImagesPath($data, $expected)
    {
        if ($expected['result']) {
            $this->validatorMock->expects($this->never())
                ->method('isValid');
        } else {
            $this->validatorMock->expects($this->once())
                ->method('isValid')
                ->with($data['additional_images'])
                ->willReturn(false);
        }

        $result = $this->media->isValid($data);
        $this->assertEquals($expected['result'], $result);
        $messages = $this->media->getMessages();
        $this->assertEquals($expected['messages'], $messages);
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider isValidAdditionalImagesUrlDataProvider
     */
    public function testIsValidAdditionalImagesUrl($data, $expected)
    {
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($data['additional_images'])
            ->willReturn($expected['result']);

        $result = $this->media->isValid($data);
        $this->assertEquals($expected['result'], $result);
        $messages = $this->media->getMessages();
        $this->assertEquals($expected['messages'], $messages);
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
            ],
        ];
    }

    /**
     * @return array
     */
    public function isValidAdditionalImagesPathDataProvider()
    {
        return [
            'additional_images' => [
                ['additional_images' => 'image1.png,image2.jpg'],
                ['result' => true, 'messages' => []]
            ],
            'additional_images_fail' => [
                ['additional_images' => 'image1.png|image2.jpg|image3.gif'],
                ['result' => false, 'messages' => [0 => 'additional_images']]
            ],
        ];
    }

    /**
     * @return array
     */
    public function isValidAdditionalImagesUrlDataProvider()
    {
        return [
            'additional_images_wrong_domain' => [
                ['additional_images' => 'https://example/images/some-name.jpg'],
                ['result' => false, 'messages' => [0 => 'additional_images']],
            ],
            'additional_images_url_multiple_underscores' => [
                ['additional_images' => 'https://example.com/images/some-name__with___multiple____underscores.jpg'],
                ['result' => true, 'messages' => []]
            ]
        ];
    }
}
