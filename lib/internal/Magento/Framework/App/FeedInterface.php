<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface FeedInterface
{
    const DEFAULT_FORMAT = 'xml';

    /**
     * @param string $format
     * @return string
     */
    public function getFormattedContentAs(string $format = self::DEFAULT_FORMAT): string;
}
