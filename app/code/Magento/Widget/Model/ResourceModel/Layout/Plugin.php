<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Model\ResourceModel\Layout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface as PageLayoutConfigBuilder;

class Plugin
{
    /**
     * @var Update
     */
    private $update;

    /**
     * @var PageLayoutConfigBuilder
     */
    private $pageLayoutConfigBuilder;

    /**
     * @var array|null
     */
    private $pageLayouts;

    /**
     * @param Update $update
     * @param PageLayoutConfigBuilder|null $pageLayoutConfigBuilder
     */
    public function __construct(Update $update, ?PageLayoutConfigBuilder $pageLayoutConfigBuilder = null)
    {
        $this->update = $update;
        $this->pageLayoutConfigBuilder = $pageLayoutConfigBuilder
            ?? ObjectManager::getInstance()->get(PageLayoutConfigBuilder::class);
    }

    /**
     * Around update
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
        if ($this->isInheritedPageLayoutHandle($subject, (string)$handle)) {
            return '';
        }
        return $this->update->fetchUpdatesByHandle($handle, $subject->getTheme(), $subject->getScope());
    }

    /**
     * Whether the handle is a page layout reached through another page layout's <update handle="..."/>
     *
     * Updates assigned to a page layout apply only to pages using exactly that layout, not to the layouts built on it.
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @param string $handle
     * @return bool
     */
    private function isInheritedPageLayoutHandle(
        \Magento\Framework\View\Model\Layout\Merge $subject,
        string $handle
    ): bool {
        if (in_array($handle, $subject->getHandles(), true)) {
            return false;
        }
        if ($this->pageLayouts === null) {
            $this->pageLayouts = array_map(
                'strval',
                array_keys($this->pageLayoutConfigBuilder->getPageLayoutsConfig()->getPageLayouts())
            );
        }
        return in_array($handle, $this->pageLayouts, true);
    }
}
