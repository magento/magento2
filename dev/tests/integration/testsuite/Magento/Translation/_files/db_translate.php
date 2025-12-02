<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/** @var \Magento\Translation\Model\ResourceModel\StringUtils $translateString */
$translateString = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Translation\Model\ResourceModel\StringUtils::class
);
$translateString->saveTranslate('Fixture String', 'Fixture Db Translation');
