<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\Exception\FileSystemException;

/**
 * Interface for parsing comments in the configuration file.
 * @since 2.2.0
 */
interface CommentParserInterface
{
    /**
     * Retrieve config list from file comments.
     *
     * @param string $fileName
     * @return array
     * @throws FileSystemException
     * @since 2.2.0
     */
    public function execute($fileName);
}
