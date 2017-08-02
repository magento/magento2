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
 * @since 2.0.0
 */
interface ProductMetadataInterface
{
    /**
     * Get Product version
     *
     * @return string
     * @since 2.0.0
     */
    public function getVersion();

    /**
     * Get Product edition
     *
     * @return string
     * @since 2.0.0
     */
    public function getEdition();

    /**
     * Get Product name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();
}
