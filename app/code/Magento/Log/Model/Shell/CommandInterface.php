<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Log\Model\Shell;

interface CommandInterface
{
    /**
     * Execute command
     *
     * @return string
     */
    public function execute();
}
