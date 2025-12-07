<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

$variable = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Variable\Model\Variable::class
);
$variable->setCode(
    'variable_code'
)->setName(
    'Variable Name'
)->setPlainValue(
    'Plain Value'
)->setHtmlValue(
    'HTML Value'
)->save();
