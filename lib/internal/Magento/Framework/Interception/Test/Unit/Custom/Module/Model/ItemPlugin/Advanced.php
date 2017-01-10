<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
