<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile

namespace Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin;

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

    /**
     * @param $subject
     * @param $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetName($subject)
    {
        //
    }
}
