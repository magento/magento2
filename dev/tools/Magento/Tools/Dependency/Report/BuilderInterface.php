<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency\Report;

/**
 *  Builder Interface
 */
interface BuilderInterface
{
    /**
     * Build a report
     *
     * @param array $options
     * @return void
     */
    public function build(array $options);
}
