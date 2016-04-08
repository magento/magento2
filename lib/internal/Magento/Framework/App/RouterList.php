<?php
/**
 * Router list
 * Used as a container for list of routers
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

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
        $this->routerList = $routerList;
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
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return RouterInterface
     */
    public function current()
    {
        return $this->getRouterInstance($this->key());
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->routerList);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return string|int|null
     */
    public function key()
    {
        return key($this->routerList);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return !!current($this->routerList);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
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
        if ((int)$routerDataFirst['sortOrder'] == (int)$routerDataSecond['sortOrder']) {
            return 0;
        }

        if ((int)$routerDataFirst['sortOrder'] < (int)$routerDataSecond['sortOrder']) {
            return -1;
        } else {
            return 1;
        }
    }
}
