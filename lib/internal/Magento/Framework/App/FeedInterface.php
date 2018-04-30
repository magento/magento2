<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Feed interface
 */
interface FeedInterface
{
    /**
     * Returns the formatted feed content
     *
     * @return string
     */
    public function getFormattedContent() : string;
}
