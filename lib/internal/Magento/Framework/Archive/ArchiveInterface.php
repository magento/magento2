<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface for work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

/**
 * @api
 */
interface ArchiveInterface
{
    /**
     * Pack file or directory.
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function pack($source, $destination);

    /**
     * Unpack file or directory.
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function unpack($source, $destination);
}
