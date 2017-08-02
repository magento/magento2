<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report;

/**
 *  Builder Interface
 * @since 2.0.0
 */
interface BuilderInterface
{
    /**
     * Build a report
     *
     * @param array $options
     * @return void
     * @since 2.0.0
     */
    public function build(array $options);
}
