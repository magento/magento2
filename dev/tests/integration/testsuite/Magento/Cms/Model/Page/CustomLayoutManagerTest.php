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
use Magento\Framework\View\Result\PageFactory as PageResultFactory;
use Magento\Framework\View\Model\Layout\MergeFactory;
use Magento\Framework\View\Model\Layout\Merge;

/**
 * Test the manager.
 */
class CustomLayoutManagerTest extends TestCase
{
    /**
     * @var CustomLayoutRepositoryInterface
     */
    private $repo;

    /**
     * @var CustomLayoutManagerInterface
     */
    private $manager;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var PageResultFactory
     */
    private $resultFactory;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->resultFactory = $objectManager->get(PageResultFactory::class);
        //Mocking available list of files for the page.
        $handles = [
            'cms_page_view_selectable_page100_select1',
            'cms_page_view_selectable_page100_select2'
        ];
        $processor = $this->getMockBuilder(Merge::class)->disableOriginalConstructor()->getMock();
        $processor->method('getAvailableHandles')->willReturn($handles);
        $processorFactory = $this->getMockBuilder(MergeFactory::class)->disableOriginalConstructor()->getMock();
        $processorFactory->method('create')->willReturn($processor);

        $this->manager = $objectManager->create(
            CustomLayoutManagerInterface::class,
            ['layoutProcessorFactory' => $processorFactory]
        );
        $this->repo = $objectManager->create(
            CustomLayoutRepositoryInterface::class,
            ['manager' => $this->manager]
        );
        $this->pageFactory = $objectManager->get(PageFactory::class);
        $this->identityMap = $objectManager->get(IdentityMap::class);
    }

    /**
     * Test updating a page's custom layout.
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @throws \Throwable
     * @return void
     */
    public function testCustomLayoutUpdate(): void
    {
        /** @var Page $page */
        $page = $this->pageFactory->create(['customLayoutRepository' => $this->repo]);
        $page->load('page100', 'identifier');
        $pageId = (int)$page->getId();
        $this->identityMap->add($page);
        //Set file ID
        $this->repo->save(new CustomLayoutSelected($pageId, 'select2'));

        //Test handles
        $result = $this->resultFactory->create();
        $this->manager->applyUpdate($result, $this->repo->getFor($pageId));
        $this->identityMap->remove((int)$page->getId());
        $this->assertContains(
            'cms_page_view_selectable_page100_select2',
            $result->getLayout()->getUpdate()->getHandles()
        );
    }
}
