<?php
/**
 * Created by PhpStorm.
 * User: pganapat
 * Date: 9/25/19
 * Time: 7:42 PM
 */

namespace Magento\Quote\Model\Quote\Item\Plugin;

use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartInterface;

class Discount
{

    public function  beforeSave(CartItemPersister $subject, CartInterface $quote, CartItemInterface $cartItem) {
        $extension = $cartItem->getExtensionAttributes();
        $cartItem->setDiscounts(\GuzzleHttp\json_encode($extension->getDiscounts()));
        return [$quote, $cartItem];
    }
}