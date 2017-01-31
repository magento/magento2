<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Block;

use Magento\Translation\Block\Js;

class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Js
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileManagerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configMock = $this->getMockBuilder('Magento\Translation\Model\Js\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileManagerMock = $this->getMockBuilder('\Magento\Translation\Model\FileManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            'Magento\Translation\Block\Js',
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
}
