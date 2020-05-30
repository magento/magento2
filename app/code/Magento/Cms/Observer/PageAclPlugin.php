<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Observer;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page\Authorization;

/**
 * Perform additional authorization before saving a page.
 */
class PageAclPlugin
{
    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * @param Authorization $authorization
     */
    public function __construct(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Authorize saving before it is executed.
     *
     * @param PageRepositoryInterface $subject
     * @param PageInterface $page
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(PageRepositoryInterface $subject, PageInterface $page): array
    {
        $this->authorization->authorizeFor($page);

        return [$page];
    }
}
