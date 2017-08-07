<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface FeedInterface
{
    /**
     * @return string
     */
    public function getFormatedContentAs(
        $format = FeedOutputFormatsInterface::DEFAULT_FORMAT
    );
}
