<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Controller\Bml;

/**
 * Class \Magento\Paypal\Controller\Bml\Start
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
            'express',
            'paypal',
            [
                'bml' => 1,
                'button' => $this->getRequest()->getParam('button')
            ]
        );
    }
}
