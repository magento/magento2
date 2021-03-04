<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Block;

use Magento\Translation\Block\Js;

class JsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Js
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileManagerMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configMock = $this->getMockBuilder(\Magento\Translation\Model\Js\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileManagerMock = $this->getMockBuilder(\Magento\Translation\Model\FileManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            \Magento\Translation\Block\Js::class,
            [
                'config' => $this->configMock,
                'fileManager' => $this->fileManagerMock
            ]
        );
    }

    public function testIsDictionaryStrategy()
    {
        $this->configMock->expects($this->once())
            ->method('dictionaryEnabled')
            ->willReturn(true);
        $this->assertTrue($this->model->dictionaryEnabled());
    }

    public function testGetTranslationFileTimestamp()
    {
        $this->fileManagerMock->expects($this->once())
            ->method('getTranslationFileTimestamp')
            ->willReturn(1445736974);
        $this->assertEquals(1445736974, $this->model->getTranslationFileTimestamp());
    }

    public function testGetTranslationFilePath()
    {
        $this->fileManagerMock->expects($this->once())
            ->method('getTranslationFilePath')
            ->willReturn('frontend/Magento/luma/en_EN');
        $this->assertEquals('frontend/Magento/luma/en_EN', $this->model->getTranslationFilePath());
    }

    public function testGetTranslationFileVersion()
    {
        $version = sha1('translationFile');

        $this->fileManagerMock->expects($this->once())
            ->method('getTranslationFileVersion')
            ->willReturn($version);
        $this->assertEquals($version, $this->model->getTranslationFileVersion());
    }
}
