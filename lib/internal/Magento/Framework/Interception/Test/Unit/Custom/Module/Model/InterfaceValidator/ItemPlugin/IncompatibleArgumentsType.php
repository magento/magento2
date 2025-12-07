<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin;

use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments;

class IncompatibleArgumentsType
{
    /**
     * @param ItemWithArguments $subject
     * @param array $names
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(
        ItemWithArguments $subject,
        array $names
    ) {
        return count($names);
    }
}
