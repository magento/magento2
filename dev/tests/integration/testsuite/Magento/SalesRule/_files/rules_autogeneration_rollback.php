<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');
/** @var $salesRule \Magento\SalesRule\Model\Rule */
$salesRule = $registry->registry('_fixture/Magento_SalesRule_Api_RuleRepository');
$salesRule->delete();
$registry->unregister('_fixture/Magento_SalesRule_Api_RuleRepository');
