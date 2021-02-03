<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Controller\Adminhtml\Dashboard;

use Magento\ReleaseNotification\Model\ContentProvider\Http\HttpContentProvider;
use Magento\ReleaseNotification\Model\ContentProviderInterface;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\ObjectManager;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @var HttpContentProvider | \PHPUnit\Framework\MockObject\MockObject
     */
    private $contentProviderMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->contentProviderMock = $this->getMockBuilder(HttpContentProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->addSharedInstance($this->contentProviderMock, HttpContentProvider::class);
    }

    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(ContentProviderInterface::class);
        parent::tearDown();
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testExecute()
    {
        $content = include __DIR__ . '/../../../_files/validContent.php';

        CacheCleaner::cleanAll();
        $this->contentProviderMock->expects($this->any())
            ->method('getContent')
            ->willReturn($content);

        $this->dispatch('backend/admin/dashboard/index/');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $actual = $this->getResponse()->getBody();
        $this->assertStringContainsString('1-mainContent title', $actual);
        $this->assertStringContainsString('2-mainContent title', $actual);
        $this->assertStringContainsString('3-mainContent title', $actual);
        $this->assertStringContainsString('4-mainContent title', $actual);
    }

    public function testExecuteEmptyContent()
    {
        CacheCleaner::cleanAll();
        $this->contentProviderMock->expects($this->any())
            ->method('getContent')
            ->willReturn('[]');

        $this->dispatch('backend/admin/dashboard/index/');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $actual = $this->getResponse()->getBody();
        $this->assertStringContainsString('"autoOpen":false', $actual);
    }

    public function testExecuteFalseContent()
    {
        CacheCleaner::cleanAll();
        $this->contentProviderMock->expects($this->any())
            ->method('getContent')
            ->willReturn(false);

        $this->dispatch('backend/admin/dashboard/index/');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $actual = $this->getResponse()->getBody();
        $this->assertStringContainsString('"autoOpen":false', $actual);
    }
}
