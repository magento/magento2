<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Test\Unit\Model\Inline;

/**
 * @covers \Magento\Translation\Model\Inline\CacheManager
 */
class CacheManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Translation\Model\Inline\CacheManager
     */
    protected $model;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Translate\ResourceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $translateResourceMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeResolverMock;

    /**
     * @var \Magento\Translation\Model\FileManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileManagerMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->translateResourceMock = $this->getMockBuilder(\Magento\Framework\Translate\ResourceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->fileManagerMock = $this->getMockBuilder(\Magento\Translation\Model\FileManager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Translation\Model\Inline\CacheManager::class,
            [
                'eventManager' => $this->eventManagerMock,
                'translateResource' => $this->translateResourceMock,
                'localeResolver' => $this->localeResolverMock,
                'fileManager' => $this->fileManagerMock
            ]
        );
    }

    public function testUpdateAndGetTranslations()
    {
        $translations = ['phrase1' => 'translated1', 'phrase2' => 'translated2'];

        $this->eventManagerMock->expects($this->once())->method('dispatch');
        $this->translateResourceMock->expects($this->once())->method('getTranslationArray')->willReturn($translations);
        $this->localeResolverMock->expects($this->once())->method('getLocale')->willReturn('en_US');
        $this->fileManagerMock->expects($this->once())->method('updateTranslationFileContent');
        $this->assertEquals($translations, $this->model->updateAndGetTranslations());
    }
}
