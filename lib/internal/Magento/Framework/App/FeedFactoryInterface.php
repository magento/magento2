<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Feed factory interface
 */
interface FeedFactoryInterface
{
    /**
     * RSS feed input format
     */
    const FORMAT_RSS = 'rss';

    /**
     * Returns FeedInterface object from a custom array
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @param  array  $data
     * @param  string $format
     * @return FeedInterface
     */
    public function create(
        array $data,
        $format = self::FORMAT_RSS
    );
}
