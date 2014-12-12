<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
