<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Attribute;

/**
 * Resolve an optional object dependency when no constructor argument is configured.
 *
 * This attribute only has meaning on constructor parameters. The property target
 * allows reflection to instantiate attributes copied onto promoted properties.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class Autowire
{
}
