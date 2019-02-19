<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit\Api;

/**
 * Unit test class for \Magento\Framework\Api\ImageContentValidator
 */
class ImageContentValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\ImageContentValidator
     */
    protected $imageContentValidator;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->imageContentValidator = $this->objectManager->getObject(
            \Magento\Framework\Api\ImageContentValidator::class
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image content must be valid base64 encoded data.
     */
    public function testIsValidEmptyContent()
    {
        $imageContent = $this->getMockBuilder(\Magento\Framework\Api\Data\ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn('');

        $this->imageContentValidator->isValid($imageContent);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image content must be valid base64 encoded data.
     */
    public function testIsValidEmptyProperties()
    {
        $imageContent = $this->getMockBuilder(\Magento\Framework\Api\Data\ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn('testImageData');

        $this->imageContentValidator->isValid($imageContent);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image MIME type is not valid or not supported.
     */
    public function testIsValidInvalidMIMEType()
    {
        $pathToImageFile = __DIR__ . '/_files/image.jpg';
        $encodedData = @base64_encode(file_get_contents($pathToImageFile));

        $imageContent = $this->getMockBuilder(\Magento\Framework\Api\Data\ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn($encodedData);
        $imageContent->expects($this->any())
            ->method('getType')
            ->willReturn('invalidType');

        $this->imageContentValidator->isValid($imageContent);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Provided image name contains forbidden characters.
     */
    public function testIsValidInvalidName()
    {
        $pathToImageFile = __DIR__ . '/_files/image.jpg';
        $encodedData = @base64_encode(file_get_contents($pathToImageFile));

        $imageContent = $this->getMockBuilder(\Magento\Framework\Api\Data\ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn($encodedData);
        $imageContent->expects($this->any())
            ->method('getName')
            ->willReturn('invalid:Name');
        $imageContent->expects($this->any())
            ->method('getType')
            ->willReturn('image/jpeg');

        $this->imageContentValidator->isValid($imageContent);
    }

    public function testIsValid()
    {
        $pathToImageFile = __DIR__ . '/_files/image.jpg';
        $encodedData = @base64_encode(file_get_contents($pathToImageFile));

        $imageContent = $this->getMockBuilder(\Magento\Framework\Api\Data\ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn($encodedData);
        $imageContent->expects($this->any())
            ->method('getName')
            ->willReturn('validName');
        $imageContent->expects($this->any())
            ->method('getType')
            ->willReturn('image/jpeg');

        $this->assertTrue($this->imageContentValidator->isValid($imageContent));
    }
}
