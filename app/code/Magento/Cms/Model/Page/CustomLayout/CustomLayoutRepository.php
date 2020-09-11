<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page\CustomLayout;

use Magento\Cms\Model\Page as PageModel;
use Magento\Cms\Model\PageFactory as PageModelFactory;
use Magento\Cms\Model\Page\CustomLayout\Data\CustomLayoutSelectedInterface;
use Magento\Cms\Model\Page\CustomLayout\Data\CustomLayoutSelected;
use Magento\Cms\Model\Page\CustomLayoutRepositoryInterface;
use Magento\Cms\Model\Page\IdentityMap;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Cms\Model\Page\CustomLayoutManagerInterface;

/**
 * @inheritDoc
 */
class CustomLayoutRepository implements CustomLayoutRepositoryInterface
{
    /**
     * @var Page
     */
    private $pageRepository;

    /**
     * @var PageModelFactory;
     */
    private $pageFactory;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @var CustomLayoutManagerInterface
     */
    private $manager;

    /**
     * @param Page $pageRepository
     * @param PageModelFactory $factory
     * @param IdentityMap $identityMap
     * @param CustomLayoutManagerInterface $manager
     */
    public function __construct(
        Page $pageRepository,
        PageModelFactory $factory,
        IdentityMap $identityMap,
        CustomLayoutManagerInterface $manager
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageFactory = $factory;
        $this->identityMap = $identityMap;
        $this->manager = $manager;
    }

    /**
     * Find page model by ID.
     *
     * @param int $id
     * @return PageModel
     * @throws NoSuchEntityException
     */
    private function findPage(int $id): PageModel
    {
        if (!$page = $this->identityMap->get($id)) {
            /** @var PageModel $page */
            $this->pageRepository->load($page = $this->pageFactory->create(), $id);
            if (!$page->getIdentifier()) {
                throw NoSuchEntityException::singleField('id', $id);
            }
        }

        return $page;
    }

    /**
     * Check whether the page can use this layout.
     *
     * @param PageModel $page
     * @param string $layoutFile
     * @return bool
     */
    private function isLayoutValidFor(PageModel $page, string $layoutFile): bool
    {
        return in_array($layoutFile, $this->manager->fetchAvailableFiles($page), true);
    }

    /**
     * Save new custom layout file value for a page.
     *
     * @param int $pageId
     * @param string|null $layoutFile
     * @throws LocalizedException
     * @throws \InvalidArgumentException When invalid file was selected.
     * @throws NoSuchEntityException
     */
    private function saveLayout(int $pageId, ?string $layoutFile): void
    {
        $page = $this->findPage($pageId);
        if ($layoutFile !== null && !$this->isLayoutValidFor($page, $layoutFile)) {
            throw new \InvalidArgumentException(
                $layoutFile .' is not available for page #' .$pageId
            );
        }

        if ($page->getData('layout_update_selected') != $layoutFile) {
            $page->setData('layout_update_selected', $layoutFile);
            $this->pageRepository->save($page);
        }
    }

    /**
     * @inheritDoc
     */
    public function save(CustomLayoutSelectedInterface $layout): void
    {
        $this->saveLayout($layout->getPageId(), $layout->getLayoutFileId());
    }

    /**
     * Validate layout update of given page model.
     *
     * @param PageModel $page
     * @return void
     * @throws LocalizedException
     */
    public function validateLayoutSelectedFor(PageModel $page): void
    {
        $layoutFile = $page->getData('layout_update_selected');
        if ($layoutFile && (!$page->getId() || !$this->isLayoutValidFor($page, $layoutFile))) {
            throw new LocalizedException(__('Invalid Custom Layout Update selected'));
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteFor(int $pageId): void
    {
        $this->saveLayout($pageId, null);
    }

    /**
     * @inheritDoc
     */
    public function getFor(int $pageId): CustomLayoutSelectedInterface
    {
        $page = $this->findPage($pageId);
        if (!$page['layout_update_selected']) {
            throw new NoSuchEntityException(
                __('Page "%id" doesn\'t have custom layout assigned', ['id' => $page->getIdentifier()])
            );
        }

        return new CustomLayoutSelected($pageId, $page['layout_update_selected']);
    }
}
