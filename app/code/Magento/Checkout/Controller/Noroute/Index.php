<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Controller\Noroute;

use \Magento\Framework\Exception\NotFoundException;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Checkout page not found controller
     *
     * @throws NotFoundException
     * @return void
     * @codeCoverageIgnore
     */
    public function execute()
    {
        throw new NotFoundException(__('Page not found.'));
    }
}
