<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
