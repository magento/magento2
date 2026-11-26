<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Model\Address\Config;

/**
 * Test double for late static binding of Config constants.
 */
class ConfigTesting extends Config
{
    public const XML_PATH_ADDRESS_TEMPLATE = 'click_and_collect/address_templates/';

    public const DEFAULT_ADDRESS_RENDERER = 'Magento\\Custom\\Block\\Address\\Renderer';
}
