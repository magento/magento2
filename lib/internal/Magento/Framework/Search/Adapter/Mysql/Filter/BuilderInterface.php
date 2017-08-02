<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * Interface \Magento\Framework\Search\Adapter\Mysql\Filter\BuilderInterface
 *
 * @since 2.0.0
 */
interface BuilderInterface
{
    /**
     * @param RequestFilterInterface $filter
     * @param string $conditionType
     * @return string
     * @since 2.0.0
     */
    public function build(RequestFilterInterface $filter, $conditionType);
}
