<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

/**
 * Class PlaceTesting extended test class, used to substitute calls to parent methods
 */
class PlaceTesting extends Place
{
    /**
     * {@inheritdoc}
     * This method tested in Magento\Sales\Controller\Adminhtml\Order\CreateTest
     */
    protected function _processActionData($action = null)
    {
        //
    }
}
