<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\RequestInterface;

/**
 * Build base Query for Index
 * @since 2.0.0
 */
interface IndexBuilderInterface
{
    /**
     * Build index query
     *
     * @param RequestInterface $request
     * @return Select
     * @since 2.0.0
     */
    public function build(RequestInterface $request);
}
