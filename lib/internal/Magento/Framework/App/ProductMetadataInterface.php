<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Magento application product metadata
 *
 * @api
 * @since 100.0.2
 */
interface ProductMetadataInterface
{
    /**
     * Get Product version
     *
     * @return string
     */
    public function getVersion();

    /**
     * Get Product edition
     *
     * @return string
     */
    public function getEdition();

    /**
     * Get Product name
     *
     * @return string
     */
    public function getName();
}
