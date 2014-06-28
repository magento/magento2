<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Product\Attribute\Media\Data;

class GalleryEntryContentValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GalleryEntryContentValidator
     */
    private $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entryContentMock;

    /**
     * @var string
     */
    private $testImagePath;

    protected function setUp()
    {
        $this->validator = new GalleryEntryContentValidator();
        $this->entryContentMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntryContent',
            array(),
            array(),
            '',
            false
        );
        $this->testImagePath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'magento_image.jpg';
    }

    public function testIsValid()
    {
        $this->entryContentMock->expects($this->any())->method('getData')->will($this->returnValue(
            base64_encode(file_get_contents($this->testImagePath))
        ));
        $this->entryContentMock->expects($this->any())->method('getName')->will($this->returnValue(
            'valid_name'
        ));
        $this->entryContentMock->expects($this->any())->method('getMimeType')->will($this->returnValue(
            'image/jpeg'
        ));
        $this->assertTrue($this->validator->isValid($this->entryContentMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image content must be valid base64 encoded data.
     */
    public function testIsValidThrowsExceptionIfProvidedContentIsNotBase64Encoded()
    {
        $this->entryContentMock->expects($this->any())->method('getData')->will($this->returnValue(
            'not_a_base64_encoded_content'
        ));
        $this->entryContentMock->expects($this->any())->method('getName')->will($this->returnValue(
            'valid_name'
        ));
        $this->entryContentMock->expects($this->any())->method('getMimeType')->will($this->returnValue(
            'image/jpeg'
        ));
        $this->assertTrue($this->validator->isValid($this->entryContentMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image content must be valid base64 encoded data.
     */
    public function testIsValidThrowsExceptionIfProvidedContentIsNotAnImage()
    {
        $this->entryContentMock->expects($this->any())->method('getData')->will($this->returnValue(
            base64_encode('not_an_image_data')
        ));
        $this->entryContentMock->expects($this->any())->method('getName')->will($this->returnValue(
            'valid_name'
        ));
        $this->entryContentMock->expects($this->any())->method('getMimeType')->will($this->returnValue(
            'image/jpeg'
        ));
        $this->assertTrue($this->validator->isValid($this->entryContentMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image MIME type is not valid or not supported.
     */
    public function testIsValidThrowsExceptionIfProvidedImageHasWrongMimeType()
    {
        $this->entryContentMock->expects($this->any())->method('getData')->will($this->returnValue(
            base64_encode(file_get_contents($this->testImagePath))
        ));
        $this->entryContentMock->expects($this->any())->method('getName')->will($this->returnValue(
            'valid_name'
        ));
        $this->entryContentMock->expects($this->any())->method('getMimeType')->will($this->returnValue(
            'wrong_mime_type'
        ));
        $this->assertTrue($this->validator->isValid($this->entryContentMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Provided image name contains forbidden characters.
     * @dataProvider getInvalidImageNames
     * @param string $imageName
     */
    public function testIsValidThrowsExceptionIfProvidedImageNameContainsForbiddenCharacters($imageName)
    {
        $this->entryContentMock->expects($this->any())->method('getData')->will($this->returnValue(
            base64_encode(file_get_contents($this->testImagePath))
        ));
        $this->entryContentMock->expects($this->any())->method('getName')->will($this->returnValue(
            $imageName
        ));
        $this->entryContentMock->expects($this->any())->method('getMimeType')->will($this->returnValue(
            'image/jpeg'
        ));
        $this->assertTrue($this->validator->isValid($this->entryContentMock));
    }

    /**
     * @return array
     */
    public function getInvalidImageNames()
    {
        return array(
            array('test/test'),
            array('test\test'),
            array('test:test'),
            array('test"test'),
            array('test*test'),
            array('test;test'),
            array('test(test'),
            array('test)test'),
            array('test<test'),
            array('test>test'),
            array('test?test'),
            array('test{test'),
            array('test}test'),
            array('test|test'),
            array('test|test'),
        );
    }
}
