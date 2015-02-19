<?php
/**
 * Magento application product metadata
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\AppInterface;

class ProductMetadata implements ProductMetadataInterface
{
    const EDITION_NAME  = 'Community';

    /**
     * Get Product version
     *
     * @return string
     */
    public function getVersion()
    {
        return AppInterface::VERSION;
    }

    /**
     * Get Product edition
     *
     * @return string
     */
    public function getEdition()
    {
        return self::EDITION_NAME;
    }
}
