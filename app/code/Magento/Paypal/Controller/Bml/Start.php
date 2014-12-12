<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Paypal\Controller\Bml;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * Action for Bill Me Later checkout button (product view and shopping cart pages)
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward(
            'start',
            'payflowexpress',
            'paypal',
            [
                'bml' => 1,
                'button' => $this->getRequest()->getParam('button')
            ]
        );
    }
}
