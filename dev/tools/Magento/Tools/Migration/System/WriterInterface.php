<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
