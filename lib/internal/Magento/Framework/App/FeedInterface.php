<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
