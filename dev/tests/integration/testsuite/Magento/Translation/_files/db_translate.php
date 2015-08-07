<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Translation\Model\Resource\StringUtils $translateString */
$translateString = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Translation\Model\Resource\StringUtils'
);
$translateString->saveTranslate('Fixture String', 'Fixture Db Translation');
