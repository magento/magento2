<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Eav\Attribute'
);
$model->setName('system_attribute')->setId(3)->setEntityTypeId(4)->setIsUserDefined(0)->setApplyTo(['simple']);
$model->save();
