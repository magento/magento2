<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\Exception\FileSystemException;

/**
 * Interface CommentParserInterface
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
