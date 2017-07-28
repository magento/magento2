<?php
/**
 * Interface of response sending file content
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

/**
 * Interface \Magento\Framework\App\Response\FileInterface
 *
 * @since 2.0.0
 */
interface FileInterface extends HttpInterface
{
    /**
     * Set path to the file being sent
     *
     * @param string $path
     * @return void
     * @since 2.0.0
     */
    public function setFilePath($path);
}
