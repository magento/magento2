<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File\Export;

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
