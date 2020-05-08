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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Model\Layout\MergeFactory;
use Magento\TestFramework\Cms\Model\CustomLayoutManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test the repository.
 */
class CustomLayoutRepositoryTest extends TestCase
{
    /**
     * @var CustomLayoutRepositoryInterface
     */
    private $repo;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var CustomLayoutManager
     */
    private $fakeManager;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fakeManager = $objectManager->get(CustomLayoutManager::class);
        $this->repo = $objectManager->create(CustomLayoutRepositoryInterface::class, ['manager' => $this->fakeManager]);
        $this->pageFactory = $objectManager->get(PageFactory::class);
        $this->identityMap = $objectManager->get(IdentityMap::class);
    }

    /**
     * Create page instance.
     *
     * @param string $id
     * @return Page
     */
    private function createPage(string $id): Page
    {
        $page = $this->pageFactory->create(['customLayoutRepository' => $this->repo]);
        $page->load($id, 'identifier');
        $this->identityMap->add($page);

        return $page;
    }

    /**
     * Test updating a page's custom layout.
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @return void
     */
    public function testCustomLayout(): void
    {
        $page = $this->createPage('page100');
        $pageId = (int)$page->getId();
        $this->fakeManager->fakeAvailableFiles($pageId, ['select1', 'select2']);

        //Invalid file ID
        $exceptionRaised = null;
        try {
            $this->repo->save(new CustomLayoutSelected($pageId, 'some_file'));
        } catch (\Throwable $exception) {
            $exceptionRaised = $exception;
        }
        $this->assertNotEmpty($exceptionRaised);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exceptionRaised);

        //Set file ID
        $this->repo->save(new CustomLayoutSelected($pageId, 'select2'));

        //Test saved
        $saved = $this->repo->getFor($pageId);
        $this->assertEquals('select2', $saved->getLayoutFileId());

        //Removing custom file
        $this->repo->deleteFor($pageId);

        //Test saved
        $notFound = false;
        try {
            $this->repo->getFor($pageId);
        } catch (NoSuchEntityException $exception) {
            $notFound = true;
        }
        $this->assertTrue($notFound);
    }

    /**
     * Test that layout updates are saved with save method.
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @return void
     */
    public function testSaved(): void
    {
        $page = $this->createPage('page100');
        $this->fakeManager->fakeAvailableFiles((int)$page->getId(), ['select1', 'select2']);

        //Special no-update instruction
        $page->setData('layout_update_selected', '_no_update_');
        $page->save();
        $this->assertNull($page->getData('layout_update_selected'));

        //Existing file update
        $page->setData('layout_update_selected', 'select1');
        $page->save();
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->load('page100', 'identifier');
        $this->assertEquals('select1', $page->getData('layout_update_selected'));
        $this->assertEquals('select1', $this->repo->getFor((int)$page->getId())->getLayoutFileId());

        //Invalid file
        $caught = null;
        $page->setData('layout_update_selected', 'nonExisting');
        try {
            $page->save();
        } catch (\Throwable $exception) {
            $caught = $exception;
        }
        $this->assertInstanceOf(LocalizedException::class, $caught);
        $this->assertEquals($caught->getMessage(), 'Invalid Custom Layout Update selected');
    }
}
