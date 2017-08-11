<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;

/**
 * File parser factory instance
 *
 */
interface ParserFactoryInterface
{
    /**
     * Creates a file parser instance for specified file
     *
     * @param string $path
     * @param array $options
     *
     * @return ParserInterface
     */
    public function create($path, array $options = []);
}
