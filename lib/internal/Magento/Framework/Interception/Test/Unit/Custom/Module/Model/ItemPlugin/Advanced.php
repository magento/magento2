<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin;

class Advanced
{
    /**
     * @param $subject
     * @param $proceed
     * @param $argument
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetName($subject, $proceed, $argument)
    {
        return '[' . $proceed($argument) . ']';
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetName($subject, $result)
    {
        return $result . '%';
    }
}
