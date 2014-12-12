<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Interface for work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

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
