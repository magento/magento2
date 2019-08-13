<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\Page\CustomLayout\Data\CustomLayoutSelected;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File;

/**
 * Test the manager.
 */
class CustomLayoutManagerTest extends TestCase
{
    /**
     * @var CustomLayoutManagerInterface
     */
    private $manager;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        //Mocking available list of files for the page.
        $files = [
            new File('cms_page_view_selectable_page100_select1.xml', 'test'),
            new File('cms_page_view_selectable_page100_select2.xml', 'test')
        ];
        $fileCollector = $this->getMockForAbstractClass(CollectorInterface::class);
        $fileCollector->method('getFiles')
            ->willReturn($files);

        $this->manager = $objectManager->create(
            CustomLayoutManagerInterface::class,
            ['fileCollector' => $fileCollector]
        );
        $this->pageFactory = $objectManager->get(PageFactory::class);
    }

    /**
     * Test updating a page's custom layout.
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @return void
     */
    public function testCustomLayout(): void
    {
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->load('page100', 'identifier');
        $pageId = (int)$page->getId();

        //Invalid file ID
        $exceptionRaised = null;
        try {
            $this->manager->save(new CustomLayoutSelected($pageId, 'some_file'));
        } catch (\Throwable $exception) {
            $exceptionRaised = $exception;
        }
        $this->assertNotEmpty($exceptionRaised);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exceptionRaised);

        //Set file ID
        $this->manager->save(new CustomLayoutSelected($pageId, 'select2'));

        //Test handles
        $this->assertEquals(['selectable' => 'page100_select2'], $this->manager->fetchHandle($pageId));

        //Removing custom file
        $this->manager->deleteFor($pageId);

        //Test handles
        $this->assertNull($this->manager->fetchHandle($pageId));
    }
}
