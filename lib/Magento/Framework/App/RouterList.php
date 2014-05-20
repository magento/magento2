<?php
/**
 * Router list
 * Used as a container for list of routers
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App;

class RouterList implements RouterListInterface
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * List of routers
     *
     * @var array
     */
    protected $routerList;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param array $routerList
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager, array $routerList)
    {
        $this->objectManager = $objectManager;
        $this->routerList = $routerList;
        $this->routerList = array_filter(
            $routerList,
            function ($item) {
                return (!isset($item['disable']) || !$item['disable']) && $item['class'];
            }
        );
        uasort($this->routerList, array($this, 'compareRoutersSortOrder'));
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
