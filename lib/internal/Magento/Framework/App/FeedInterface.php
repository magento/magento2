<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface FeedInterface
{
    const FORMAT_XML = 'xml';

    /**
     * @return string
     */
    public function getFormattedContentAs(
        $format = self::FORMAT_XML
    );
}
