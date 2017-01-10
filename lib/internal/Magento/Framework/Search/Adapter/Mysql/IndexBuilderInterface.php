<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\RequestInterface;

/**
 * Build base Query for Index
 */
interface IndexBuilderInterface
{
    /**
     * Build index query
     *
     * @param RequestInterface $request
     * @return Select
     */
    public function build(RequestInterface $request);
}
