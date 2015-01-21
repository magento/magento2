<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System;

interface WriterInterface
{
    /**
     * @param string $fileName
     * @param string $contents
     * @return void
     */
    public function write($fileName, $contents);

    /**
     * Remove file
     *
     * @param string $fileName
     * @return void
     */
    public function remove($fileName);
}
