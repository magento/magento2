<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin;

use \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments;

class ValidPlugin
{
    /**
     * @param ItemWithArguments $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItem(
        ItemWithArguments $subject,
        $result
    ) {
        return $result . '!';
    }

    /**
     * @param ItemWithArguments $subject
     * @param $name
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(
        ItemWithArguments $subject,
        $name
    ) {
        return '|' . $name;
    }

    /**
     * @param ItemWithArguments $subject
     * @param Closure $proceed
     * @param string $name
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItem(
        ItemWithArguments $subject,
        \Closure $proceed,
        $name
    ) {
        $proceed('&' . $name . '&');
    }
}
