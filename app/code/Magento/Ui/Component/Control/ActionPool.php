<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\Control\ActionPoolInterface;

/**
 * Class ActionPool
 */
class ActionPool implements ActionPoolInterface
{
    /**
     * Actions toolbar block name
     */
    const ACTIONS_PAGE_TOOLBAR = 'page.actions.toolbar';

    /**
     * Render context
     *
     * @var Context
     */
    protected $context;

    /**
     * Actions pool
     *
     * @var Item[]
     */
    protected $items;

    /**
     * Button factory
     *
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var AbstractBlock
     */
    protected $toolbarBlock;

    /**
     * Construct
     *
     * @param Context $context
     * @param ItemFactory $itemFactory
     */
    public function __construct(Context $context, ItemFactory $itemFactory)
    {
        $this->context = $context;
        $this->itemFactory = $itemFactory;
    }

    /**
     * Get toolbar block
     *
     * @return bool|BlockInterface
     */
    public function getToolbar()
    {
        return $this->context->getPageLayout()
            ? $this->context->getPageLayout()->getBlock(static::ACTIONS_PAGE_TOOLBAR)
            : false;
    }

    /**
     * Add button
     *
     * @param string $key
     * @param array $data
     * @param UiComponentInterface $component
     * @return void
     */
    public function add($key, array $data, UiComponentInterface $component)
    {
        $data['id'] = isset($data['id']) ? $data['id'] : $key;

        $toolbar = $this->getToolbar();
        if ($toolbar !== false) {
            $this->items[$key] = $this->itemFactory->create();
            $this->items[$key]->setData($data);
            $container = $this->createContainer($key, $component);
            $toolbar->setChild($key, $container);
        }
    }

    /**
     * Remove button
     *
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Update button
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function update($key, array $data)
    {
        if (isset($this->items[$key])) {
            $this->items[$key]->setData($data);
        }
    }

    /**
     * Add html block
     *
     * @param  string $type
     * @param  string $name
     * @param  array $arguments
     * @return void
     */
    public function addHtmlBlock($type, $name = '', array $arguments = [])
    {
        $toolbar = $this->getToolbar();
        $container = $this->context->getPageLayout()->createBlock($type, $name, $arguments);
        if ($toolbar) {
            $toolbar->setChild($name, $container);
        }
    }

    /**
     * Create button container
     *
     * @param string $key
     * @param UiComponentInterface $view
     * @return Container
     */
    protected function createContainer($key, UiComponentInterface $view)
    {
        $container = $this->context->getPageLayout()->createBlock(
            'Magento\Ui\Component\Control\Container',
            'container-' . $view->getName() . '-' . $key,
            [
                'data' => [
                    'button_item' => $this->items[$key],
                    'context' => $view,
                ]
            ]
        );

        return $container;
    }
}
