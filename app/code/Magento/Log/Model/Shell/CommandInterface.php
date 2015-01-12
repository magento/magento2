<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
