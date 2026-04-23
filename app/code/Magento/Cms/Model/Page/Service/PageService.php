<?php
declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cms\Model\Page\Service;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page\CustomLayoutManagerInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Cms Page Service Class
 */
class PageService
{
    /**
     * @param PageRepositoryInterface $pageRepository
     * @param PageFactory $pageFactory
     * @param CustomLayoutManagerInterface $customLayoutManager
     */
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly PageFactory $pageFactory,
        private readonly CustomLayoutManagerInterface $customLayoutManager,
    ) {
    }

    /**
     * To get the page by its ID. If the page not exists, a new page instance is returned
     *
     * @param int $id
     * @return PageInterface
     */
    public function getPageById(int $id): PageInterface
    {
        try {
            return $this->pageRepository->getById($id);
        } catch (LocalizedException) {
            return $this->pageFactory->create();
        }
    }

    /**
     * To create pagefactory class
     *
     * @return PageInterface
     */
    public function createPageFactory(): PageInterface
    {
        return $this->pageFactory->create();
    }

    /**
     * Fetches the available custom layouts for a given page.
     *
     * @param PageInterface $page
     * @return array
     */
    public function fetchAvailableCustomLayouts(PageInterface $page): array
    {
        return $this->customLayoutManager->fetchAvailableFiles($page);
    }
}
