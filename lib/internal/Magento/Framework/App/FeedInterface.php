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
     * @return string
     */
    public function getFormatedContentAs(
        $format = self::DEFAULT_FORMAT
    );
}
