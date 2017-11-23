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
     * XML feed output format
     */
    const FORMAT_XML = 'xml';

    /**
     * @param string $format
     * 
     * @return string
     */
    public function getFormattedContentAs(
        $format = self::FORMAT_XML
    );
}
