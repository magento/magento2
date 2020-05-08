<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Translation\Block\Js;
use Magento\Translation\Model\FileManager;
use Magento\Translation\Model\Js\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JsTest extends TestCase
{
    /**
     * @var Js
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $configMock;

    /**
     * @var MockObject
     */
    protected $fileManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileManagerMock = $this->getMockBuilder(FileManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Js::class,
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
