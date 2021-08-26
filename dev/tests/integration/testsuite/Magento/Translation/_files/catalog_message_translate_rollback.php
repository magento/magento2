<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var \Magento\Translation\Model\ResourceModel\StringUtils $translateString */
$translateString = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Translation\Model\ResourceModel\StringUtils::class
);
$translateString->deleteTranslate('currentPage value must be greater than 0.', "nl_NL", 0);
