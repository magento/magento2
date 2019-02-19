<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Options;

/**
 * Generator options resolver interface
 */
interface ResolverInterface
{
    /**
     * @return array
     */
    public function getOptions();
}
