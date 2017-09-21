<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * @api
 * @since 100.0.2
 */
interface ScopedAttributeInterface
{
    const SCOPE_STORE = 0;

    const SCOPE_GLOBAL = 1;

    const SCOPE_WEBSITE = 2;
}
