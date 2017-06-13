<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Translation\Model\ResourceModel\StringUtils $translateString */
$translateString = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Translation\Model\ResourceModel\StringUtils::class
);
$translateString->saveTranslate('Fixture String', 'Fixture Db Translation');
