<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin;

use Closure;
use Magento\SalesRule\Model\Rule as ModelRule;

class Rule
{
    /**
     * @param ModelRule $subject
     * @param Closure $proceed
     * @return ModelRule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoadRelations(
        ModelRule $subject,
        Closure  $proceed
    ) {
        return $subject;
    }
}
