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
 * Manages CMS pages modification initiated by users.
 */
class SaveManager
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
     * User saves a page (bew or existing).
     *
     * @param \Magento\Cms\Api\Data\PageInterface $page
     * @return \Magento\Cms\Api\Data\PageInterface
     * @throws LocalizedException
     */
    public function save(\Magento\Cms\Api\Data\PageInterface $page): \Magento\Cms\Api\Data\PageInterface
    {
        $this->validatePage($page);

        return $this->pageRepository->save($page);
    }

    /**
     * Validate page data received from a user.
     *
     * @param PageInterface $page
     * @return void
     * @throws LocalizedException When validation failed.
     */
    public function validatePage(\Magento\Cms\Api\Data\PageInterface $page): void
    {
        //Validate design changes.
        if (!$this->authorization->isAllowed('Magento_Cms::save_design')) {
            $notAllowed = false;
            if (!$page->getId()) {
                if ($page->getLayoutUpdateXml()
                    || $page->getPageLayout()
                    || $page->getCustomTheme()
                    || $page->getCustomLayoutUpdateXml()
                    || $page->getCustomThemeFrom()
                    || $page->getCustomThemeTo()
                ) {
                    //Not allowed to set design properties value for new pages.
                    $notAllowed = true;
                }
            } else {
                $savedPage = $this->pageRepository->getById($page->getId());
                if ($page->getLayoutUpdateXml() !== $savedPage->getLayoutUpdateXml()
                    || $page->getPageLayout() !== $savedPage->getPageLayout()
                    || $page->getCustomTheme() !== $savedPage->getCustomTheme()
                    || $page->getCustomThemeTo() !== $savedPage->getCustomThemeTo()
                    || $page->getCustomThemeFrom() !== $savedPage->getCustomThemeFrom()
                    || $page->getCustomLayoutUpdateXml() !== $savedPage->getCustomLayoutUpdateXml()
                ) {
                    //Not allowed to update design settings.
                    $notAllowed = true;
                }
            }

            if ($notAllowed) {
                throw new AuthorizationException(
                    __('You are not allowed to change CMS pages design settings')
                );
            }
        }
    }
}
