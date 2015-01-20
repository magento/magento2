<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Compiler\Log\Writer;

interface WriterInterface
{
    /**
     * Output log data
     *
     * @param array $data
     * @return void
     */
    public function write(array $data);
}
