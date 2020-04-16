<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Test\Unit\Model\Inline;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\ResourceInterface;
use Magento\Translation\Model\FileManager;
use Magento\Translation\Model\Inline\CacheManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Translation\Model\Inline\CacheManager
 */
class CacheManagerTest extends TestCase
{
    /**
     * @var CacheManager
     */
    protected $model;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var ResourceInterface|MockObject
     */
    protected $translateResourceMock;

    /**
     * @var ResolverInterface|MockObject
     */
    protected $localeResolverMock;

    /**
     * @var FileManager|MockObject
     */
    protected $fileManagerMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->translateResourceMock = $this->getMockBuilder(ResourceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->fileManagerMock = $this->getMockBuilder(FileManager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            CacheManager::class,
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
