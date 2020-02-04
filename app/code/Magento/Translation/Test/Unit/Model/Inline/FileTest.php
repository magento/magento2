<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Inline;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Translation\Model\Inline\File
     */
    private $model;

    /**
     * @var \Magento\Framework\Translate\ResourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translateResourceMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolverMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    protected function setUp()
    {
        $this->translateResourceMock = $this->getMockBuilder(\Magento\Framework\Translate\ResourceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonSerializer = new \Magento\Framework\Serialize\Serializer\Json();

        $this->model = new \Magento\Translation\Model\Inline\File(
            $this->translateResourceMock,
            $this->localeResolverMock,
            $this->jsonSerializer
        );
    }

    public function testGetTranslationFileContent()
    {
        $translations = ['string' => 'translatedString'];

        $this->localeResolverMock->expects($this->atLeastOnce())->method('getLocale')->willReturn('en_US');
        $this->translateResourceMock->expects($this->atLeastOnce())->method('getTranslationArray')
            ->willReturn($translations);

        $this->assertEquals(
            $this->jsonSerializer->serialize($translations),
            $this->model->getTranslationFileContent()
        );
    }
}
