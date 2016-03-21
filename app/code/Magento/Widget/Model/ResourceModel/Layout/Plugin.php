<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\ResourceModel\Layout;

/**
 * Class Plugin
 *
 * @package Magento\Widget\Model\ResourceModel\Layout
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
