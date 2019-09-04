<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Controller\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Authorization for saving a page.
 */
class Authorization
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        AuthorizationInterface $authorization
    ) {
        $this->pageRepository = $pageRepository;
        $this->authorization = $authorization;
    }

    /**
     * Check whether the design fields have been changed.
     *
     * @param PageInterface $page
     * @param PageInterface|null $oldPage
     * @return bool
     */
    private function hasPageChanged(PageInterface $page, ?PageInterface $oldPage): bool
    {
        $oldUpdateXml = $oldPage ? $oldPage->getLayoutUpdateXml() : null;
        $oldPageLayout = $oldPage ? $oldPage->getPageLayout() : null;
        $oldCustomTheme = $oldPage ? $oldPage->getCustomTheme() : null;
        $oldLayoutUpdate = $oldPage ? $oldPage->getCustomLayoutUpdateXml() : null;
        $oldThemeFrom = $oldPage ? $oldPage->getCustomThemeFrom() : null;
        $oldThemeTo = $oldPage ? $oldPage->getCustomThemeTo() : null;

        if ($page->getLayoutUpdateXml() !== $oldUpdateXml
            || $page->getPageLayout() !== $oldPageLayout
            || $page->getCustomTheme() !== $oldCustomTheme
            || $page->getCustomLayoutUpdateXml() !== $oldLayoutUpdate
            || $page->getCustomThemeFrom() !== $oldThemeFrom
            || $page->getCustomThemeTo() !== $oldThemeTo
        ) {
            return true;
        }

        return false;
    }

    /**
     * Authorize user before updating a page.
     *
     * @param PageInterface $page
     * @return void
     * @throws AuthorizationException
     * @throws LocalizedException When it is impossible to perform authorization for given page.
     */
    public function authorizeFor(PageInterface $page): void
    {
        //Validate design changes.
        if (!$this->authorization->isAllowed('Magento_Cms::save_design')) {
            $oldPage = null;
            if ($page->getId()) {
                $oldPage = $this->pageRepository->getById($page->getId());
            }
            if ($this->hasPageChanged($page, $oldPage)) {
                throw new AuthorizationException(
                    __('You are not allowed to change CMS pages design settings')
                );
            }
        }
    }
}
