<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 *
 * Black list for the @see \Magento\Test\Integrity\DependencyTest::testUndeclared()
 */
return [
    'app/code/Magento/Paypal/Model/AbstractConfig.php' => ['Magento\Cart'],
    'app/code/Magento/Customer/Controller/Adminhtml/Index/Cart.php' => ['Magento\Cart'],
    'app/code/Magento/Customer/Controller/Adminhtml/Cart/Product/Composite/Cart.php' => ['Magento\Cart'],
    'app/code/Magento/Customer/Controller/Adminhtml/Index/Carts.php' => ['Magento\Cart'],
];
