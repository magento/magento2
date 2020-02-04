<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement implements HttpGetActionInterface
{
    /**
     * @return void
     * @codeCoverageIgnore
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
