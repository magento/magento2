<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\PageRepository;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validate a page repository
 *
 * @api
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
