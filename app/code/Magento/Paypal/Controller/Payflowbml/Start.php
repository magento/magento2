<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Controller\Payflowbml;

/**
 * Class \Magento\Paypal\Controller\Payflowbml\Start
 *
 * @since 2.0.0
 */
class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * Action for Bill Me Later checkout button (product view and shopping cart pages)
     *
     * @return void
     * @since 2.0.0
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
