<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Resource\Layout;

/**
 * Class Plugin
 *
 * @package Magento\Widget\Model\Resource\Layout
 */
class Plugin
{
    /**
     * @var \Magento\Widget\Model\Resource\Layout\Update
     */
    private $update;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    private $theme;

    /**
     * @var \Magento\Framework\App\ScopeInterface
     */
    private $store;

    /**
     * @param \Magento\Widget\Model\Resource\Layout\Update $update
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Framework\Store\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Widget\Model\Resource\Layout\Update $update,
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Framework\Store\StoreManagerInterface $storeManager
    ) {
        $this->update = $update;
        $this->theme = $theme;
        $this->store = $storeManager->getStore();
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
        return $this->update->fetchUpdatesByHandle($handle, $this->theme, $this->store);
    }
} 