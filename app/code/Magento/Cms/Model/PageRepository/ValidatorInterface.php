<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\PageRepository;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validate a page repository
 */
interface ValidatorInterface
{
    /**
     * Assert the given page valid
     *
     * @param PageInterface $page
     * @return void
     * @throws LocalizedException
     */
    public function validate(PageInterface $page): void;
}
