<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Model\ResourceModel\Layout;

use Magento\Widget\Model\ResourceModel\Layout\Update as WidgetLayoutUpdate;

/**
 * Class Plugin
 */
class Plugin
{
    /**
     * @var WidgetLayoutUpdate
     */
    private $update;

    /**
     * @param WidgetLayoutUpdate
     */
    public function __construct(
        WidgetLayoutUpdate $update
    ) {
        $this->update = $update;
    }

    /**
     * Around getDbUpdateString
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @param callable $proceed
     * @param string $handle
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetDbUpdateString(
        \Magento\Framework\View\Model\Layout\Merge $subject,
        \Closure $proceed,
        $handle
    ) {
        return $this->update->fetchUpdatesByHandle($handle, $subject->getTheme(), $subject->getScope());
    }
}
