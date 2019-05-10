<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin;

class Simple
{
    /**
     * @param $subject
     * @param $invocationResult
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetName($subject, $invocationResult)
    {
        return $invocationResult . '!';
    }
}
