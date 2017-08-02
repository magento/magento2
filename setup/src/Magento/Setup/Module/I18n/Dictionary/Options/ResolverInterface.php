<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Options;

/**
 * Generator options resolver interface
 * @since 2.0.0
 */
interface ResolverInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function getOptions();
}
