<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\Exception\FileSystemException;

/**
 * Interface for parsing comments in the configuration file.
 */
interface CommentParserInterface
{
    /**
     * Retrieve config list from file comments.
     *
     * @param string $fileName
     * @return array
     * @throws FileSystemException
     */
    public function execute($fileName);
}
