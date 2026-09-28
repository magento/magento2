<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

/** @var \Magento\Translation\Model\ResourceModel\StringUtils $translateString */
$translateString = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Translation\Model\ResourceModel\StringUtils::class
);
$translateString->saveTranslate(
    'currentPage value must be greater than 0.',
    'currentPage-waarde moet groter zijn dan 0.',
    "nl_NL",
    0
);
