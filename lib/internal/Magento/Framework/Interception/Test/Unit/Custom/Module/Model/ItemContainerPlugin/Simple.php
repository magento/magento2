<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainerPlugin;

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
        return $invocationResult . '|';
    }
}
