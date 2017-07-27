<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface FeedFactoryInterface
{

    /**
     * Returns FeedInterface object from a custom array
     * 
     * @throws \Magento\Framework\Exception\RuntimeException
     * @param  array  $data
     * @param  string $format
     * @return FeedInterface
     */
    public function importArray(
        array $data, 
        $format = FeedFormatsInterface::DEFAULT_FORMAT
    );
}
