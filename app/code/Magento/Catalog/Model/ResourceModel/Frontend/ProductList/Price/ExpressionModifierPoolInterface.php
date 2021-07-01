<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;

interface ExpressionModifierPoolInterface
{
    /**
     * @return ExpressionModifierInterface[]
     */
    public function getSortedExpressionModifiers();
}
