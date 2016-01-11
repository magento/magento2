<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Controller\Paypal;

use Magento\Framework\App\Action\Action;

/**
 * Class Review
 */
class Review extends Action
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        var_dump($this->_request->getParam('result'));
        die;
    }
}
