<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Controller\Adminhtml\Index as BaseAction;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Carts extends BaseAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin cart
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Cart::cart';

    /**
     * Get shopping carts from all websites for specified client
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
