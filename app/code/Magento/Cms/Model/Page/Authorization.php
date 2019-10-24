<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface as PageLayoutBuilder;

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
     * @var PageLayoutBuilder
     */
    private $pageLayoutBuilder;

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param AuthorizationInterface $authorization
     * @param PageLayoutBuilder $pageLayoutBuilder
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        AuthorizationInterface $authorization,
        PageLayoutBuilder $pageLayoutBuilder
    ) {
        $this->pageRepository = $pageRepository;
        $this->authorization = $authorization;
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * Check whether the design fields have been changed.
     *
     * @param PageInterface $page
     * @param PageInterface|null $oldPage
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function hasPageChanged(PageInterface $page, $oldPage): bool
    {
        if (!$oldPage) {
            //Finding default page layout value.
            $oldPageLayout = array_keys($this->pageLayoutBuilder->getPageLayoutsConfig()->getPageLayouts())[0];
            if ($page->getPageLayout() && $page->getPageLayout() !== $oldPageLayout) {
                //If page layout is set and it's not a default value - design attributes are changed.
                return true;
            }
            //Otherwise page layout is empty and is OK to save.
            $oldPageLayout = $page->getPageLayout();
        } else {
            //Compare page layout to saved value.
            $oldPageLayout = $oldPage->getPageLayout();
        }
        //Compare new values to saved values or require them to be empty
        $oldUpdateXml = $oldPage ? $oldPage->getLayoutUpdateXml() : null;
        $oldCustomTheme = $oldPage ? $oldPage->getCustomTheme() : null;
        $oldLayoutUpdate = $oldPage ? $oldPage->getCustomLayoutUpdateXml() : null;
        $oldThemeFrom = $oldPage ? $oldPage->getCustomThemeFrom() : null;
        $oldThemeTo = $oldPage ? $oldPage->getCustomThemeTo() : null;
        $oldLayoutSelected = null;
        if ($oldPage instanceof \Magento\Cms\Model\Page) {
            $oldLayoutSelected = $oldPage->getData('layout_update_selected');
        }
        $newLayoutSelected = null;
        if ($page instanceof \Magento\Cms\Model\Page) {
            $newLayoutSelected = $page->getData('layout_update_selected');
        }

        if ($page->getLayoutUpdateXml() != $oldUpdateXml
            || $page->getPageLayout() != $oldPageLayout
            || $page->getCustomTheme() != $oldCustomTheme
            || $page->getCustomLayoutUpdateXml() != $oldLayoutUpdate
            || $page->getCustomThemeFrom() != $oldThemeFrom
            || $page->getCustomThemeTo() != $oldThemeTo
            || $newLayoutSelected != $oldLayoutSelected
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
     * @throws \Magento\Framework\Exception\LocalizedException When it is impossible to perform authorization.
     */
    public function authorizeFor(PageInterface $page)
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
