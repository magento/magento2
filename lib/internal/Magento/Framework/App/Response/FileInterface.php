<?php
/**
 * Interface of response sending file content
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

interface FileInterface extends HttpInterface
{
    /**
     * Set path to the file being sent
     *
     * @param string $path
     * @return void
     */
    public function setFilePath($path);
}
