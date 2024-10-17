<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Black list for the @see \Magento\Test\Integrity\DependencyTest::testUndeclared()
 */
return [
    'app/code/Magento/Paypal/Model/AbstractConfig.php' => ['Magento\Cart'],
    'app/code/Magento/Customer/Controller/Adminhtml/Index/Cart.php' => ['Magento\Cart'],
    'app/code/Magento/Customer/Controller/Adminhtml/Cart/Product/Composite/Cart.php' => ['Magento\Cart'],
    'app/code/Magento/Customer/Controller/Adminhtml/Index/Carts.php' => ['Magento\Cart'],
];
