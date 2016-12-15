<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File\Resource;

use Magento\Mtf\ObjectManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlInterface;

/**
 * File reader interface for Magento export files.
 */
interface ReaderInterface
{
    /**
     * Url to export.php.
     */
    const URL = 'dev/tests/functional/utils/export.php';

    /**
     * Exporting files as Data object from Magento.
     *
     * @return Data[]
     */
    public function getData();
}
