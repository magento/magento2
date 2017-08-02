<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\ResourceModel\Layout;

/**
 * Class Plugin
 * @since 2.0.0
 */
class Plugin
{
    /**
     * @var \Magento\Widget\Model\ResourceModel\Layout\Update
     * @since 2.0.0
     */
    private $update;

    /**
     * @param \Magento\Widget\Model\ResourceModel\Layout\Update $update
     * @since 2.0.0
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
     * @param callable $proceed
     * @param string $handle
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function aroundGetDbUpdateString(
        \Magento\Framework\View\Model\Layout\Merge $subject,
        \Closure $proceed,
        $handle
    ) {
        return $this->update->fetchUpdatesByHandle($handle, $subject->getTheme(), $subject->getScope());
    }
}
