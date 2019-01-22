<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Widget\Model\ResourceModel\Layout;

/**
 * Class Plugin
 */
class Plugin
{
    /**
     * @var \Magento\Widget\Model\ResourceModel\Layout\Update
     */
    private $update;

    /**
     * @param \Magento\Widget\Model\ResourceModel\Layout\Update $update
     */
    public function __construct(
        \Magento\Widget\Model\ResourceModel\Layout\Update $update
    ) {
        $this->update = $update;
    }

    /**
     * Around getDbUpdateString
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @param \Closure $proceed
     * @param string $handle
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetDbUpdateString(
        \Magento\Framework\View\Model\Layout\Merge $subject,
        \Closure $proceed,
        string $handle
    ): string {
        return $this->update->fetchUpdatesByHandle($handle, $subject->getTheme(), $subject->getScope());
    }
}
