<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

/**
 * Feed factory interface
 *
 * @api
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
     * @param array $data
     * @param string $format
     * @return FeedInterface
     */
    public function create(array $data, string $format = self::FORMAT_RSS): FeedInterface;
}
