<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Used as a container for list of routers.
 */
class RouterList implements RouterListInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * List of routers
     *
     * @var RouterInterface[]
     */
    protected $routerList;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $routerList
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $routerList)
    {
        $this->objectManager = $objectManager;
        $this->routerList = array_filter(
            $routerList,
            function ($item) {
                return (!isset($item['disable']) || !$item['disable']) && $item['class'];
            }
        );
        uasort($this->routerList, [$this, 'compareRoutersSortOrder']);
    }

    /**
     * Retrieve router instance by id
     *
     * @param string $routerId
     * @return RouterInterface
     */
    protected function getRouterInstance($routerId)
    {
        if (!isset($this->routerList[$routerId]['object'])) {
            $this->routerList[$routerId]['object'] = $this->objectManager->create(
                $this->routerList[$routerId]['class']
            );
        }
        return $this->routerList[$routerId]['object'];
    }

    /**
     * @inheritDoc
     *
     * @return RouterInterface
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->getRouterInstance($this->key());
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->routerList);
    }

    /**
     * @inheritDoc
     *
     * @return string|int|null
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->routerList);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return !!current($this->routerList);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->routerList);
    }

    /**
     * Compare routers sortOrder
     *
     * @param array $routerDataFirst
     * @param array $routerDataSecond
     * @return int
     */
    protected function compareRoutersSortOrder($routerDataFirst, $routerDataSecond)
    {
        return (int)$routerDataFirst['sortOrder'] <=> (int)$routerDataSecond['sortOrder'];
    }
}
