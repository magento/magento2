<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$variable = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Variable');
$variable->setCode(
    'variable_code'
)->setName(
    'Variable Name'
)->setPlainValue(
    'Plain Value'
)->setHtmlValue(
    'HTML Value'
)->save();
