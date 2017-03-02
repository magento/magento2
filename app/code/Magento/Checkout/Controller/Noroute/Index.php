<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
