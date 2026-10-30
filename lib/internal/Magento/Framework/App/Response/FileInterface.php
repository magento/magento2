<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Response;

/**
 * Interface \Magento\Framework\App\Response\FileInterface
 *
 * @api
 */
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
