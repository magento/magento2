<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Test\Unit\Model\Inline;

/**
 * @covers \Magento\Translation\Model\Inline\CacheManager
 */
class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Translation\Model\Inline\CacheManager
     */
    protected $model;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Translate\ResourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateResourceMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolverMock;

    /**
     * @var \Magento\Translation\Model\FileManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileManagerMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->translateResourceMock = $this->getMockBuilder('Magento\Framework\Translate\ResourceInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->fileManagerMock = $this->getMockBuilder('Magento\Translation\Model\FileManager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Translation\Model\Inline\CacheManager',
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
