<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Model\ResourceModel\Layout;

use Closure;
use Magento\Framework\View\Model\Layout\Merge;

class Plugin
{
    /**
     * @param Update $update
     */
    public function __construct(
        private readonly Update $update
    ) {
    }

    /**
     * Around update
     *
     * @param Merge $subject
     * @param callable $proceed
     * @param string $handle
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetDbUpdateString(
        Merge $subject,
        Closure $proceed,
        $handle
    ) {
        return $this->update->fetchUpdatesByHandle($handle, $subject->getTheme(), $subject->getScope());
    }
}
