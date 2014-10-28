<?php
/**
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

namespace Magento\Config;

/**
 * Class FileIterator
 */
class FileIterator implements \Iterator, \Countable
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var array
     */
    protected $paths = array();

    /**
     * @param string $basePath
     * @param array $paths
     */
    public function __construct($basePath, array $paths)
    {
        $this->basePath = $basePath;
        $this->paths = $paths;
    }

    /**
     *Rewind
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->paths);
    }

    /**
     * Current
     *
     * @return string
     */
    public function current()
    {
        return file_get_contents($this->basePath . $this->key());
    }

    /**
     * Key
     *
     * @return mixed
     */
    public function key()
    {
        return current($this->paths);
    }

    /**
     * Next
     *
     * @return void
     */
    public function next()
    {
        next($this->paths);
    }

    /**
     * Valid
     *
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->key();
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();
        foreach ($this as $item) {
            $result[$this->key()] = $item;
        }
        return $result;
    }

    /**
     * Count
     *
     * @return int
     */
    public function count()
    {
        return count($this->paths);
    }
}
