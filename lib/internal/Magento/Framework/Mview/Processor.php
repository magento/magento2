<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

/**
 * Class \Magento\Framework\Mview\Processor
 *
 * @since 2.0.0
 */
class Processor implements ProcessorInterface
{
    /**
     * @var View\CollectionFactory
     * @since 2.0.0
     */
    protected $viewsFactory;

    /**
     * @param View\CollectionFactory $viewsFactory
     * @since 2.0.0
     */
    public function __construct(View\CollectionFactory $viewsFactory)
    {
        $this->viewsFactory = $viewsFactory;
    }

    /**
     * Return list of views by group
     *
     * @param string $group
     * @return ViewInterface[]
     * @since 2.0.0
     */
    protected function getViewsByGroup($group = '')
    {
        $collection = $this->viewsFactory->create();
        return $group ? $collection->getItemsByColumnValue('group', $group) : $collection->getItems();
    }

    /**
     * Materialize all views by group (all views if empty)
     *
     * @param string $group
     * @return void
     * @since 2.0.0
     */
    public function update($group = '')
    {
        foreach ($this->getViewsByGroup($group) as $view) {
            $view->update();
        }
    }

    /**
     * Clear all views' changelogs by group (all views if empty)
     *
     * @param string $group
     * @return void
     * @since 2.0.0
     */
    public function clearChangelog($group = '')
    {
        foreach ($this->getViewsByGroup($group) as $view) {
            $view->clearChangelog();
        }
    }
}
