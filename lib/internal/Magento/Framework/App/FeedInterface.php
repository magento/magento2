<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

/**
 * Feed interface
 *
 * @api
 */
interface FeedInterface
{
    /**
     * Returns the formatted feed content
     *
     * @return string
     */
    public function getFormattedContent(): string;
}
