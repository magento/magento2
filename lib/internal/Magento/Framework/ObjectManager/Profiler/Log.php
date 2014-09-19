<?php
/**
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
namespace Magento\Framework\ObjectManager\Profiler;

use Magento\Framework\ObjectManager\Profiler\Tree\Item as Item;

class Log
{
    /**
     * @var $this
     */
    protected static $instance;

    /**
     * @var Item
     */
    protected $currentItem = null;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $roots = array();

    /**
     * @var array
     */
    protected $used = array();

    /**
     * @var array
     */
    protected $stats = array('total' => 0, 'used' => 0, 'unused' => 0);

    /**
     * Constructor
     */
    public function __construct()
    {
        register_shutdown_function(array($this, 'display'));
    }

    /**
     * Retrieve instance
     *
     * @return Log
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Log();
        }
        return self::$instance;
    }

    /**
     * Log object instantiation
     *
     * @param string $class
     * @return void
     */
    public function startCreating($class)
    {
        $this->stats['total']++;
        $parent = empty($this->currentItem) ? null : $this->currentItem;
        $item = new Item($class, $parent);

        if (!$parent) {
            $this->roots[] = $item;
        }

        $this->currentItem = $item;
    }

    /**
     * Log object instantiation
     *
     * @param mixed $object
     * @return void
     */
    public function stopCreating($object)
    {
        $this->currentItem->setHash(spl_object_hash($object));
        $this->currentItem = $this->currentItem->getParent();
    }

    /**
     * Monitor object
     *
     * @param mixed $object
     * @return void
     */
    public function add($object)
    {
        $this->stats['total']++;
        if ($this->currentItem) {
            $item = new Item(get_class($object), $this->currentItem);
            $this->currentItem->addChild($item);
        } else {
            $item = new Item(get_class($object), null);
            $this->roots[] = $item;
        }
        $item->setHash(spl_object_hash($object));

    }

    /**
     * Object was invoked
     *
     * @param mixed $object
     * @return void
     */
    public function invoked($object)
    {
        $this->used[spl_object_hash($object)] = 1;
    }

    /**
     * Display results
     *
     * @return void
     */
    public function display()
    {
        $this->stats['used'] = count($this->used);
        $this->stats['unused'] = $this->stats['total'] - $this->stats['used'];
        echo '<table border="1" cellspacing="0" cellpadding="2">' . PHP_EOL;
        echo "<caption>Creation chain (Red items are never used) Total: {$this->stats['total']}\n";
        echo "Used: {$this->stats['used']} Not used: {$this->stats['unused']}</caption>";
        echo '<tbody>';
        echo "<tr><th>Instance class</th></tr>";
        foreach ($this->roots as $root) {
            $this->displayItem($root);
        }
        echo '</tbody>';
        echo '</table>';
    }

    /**
     * Display item
     *
     * @param Item $item
     * @param int $level
     * @return void
     */
    protected function displayItem(Item $item, $level = 0)
    {
        $colorStyle = isset($this->used[$item->getHash()]) ? '' : ' style="color:red" ';

        echo "<tr><td $colorStyle>" . str_repeat('·&nbsp;', $level) . $item->getClass()
            . ' - ' . $item->getHash() . '</td></tr>';

        foreach ($item->getChildren() as $child) {
            $this->displayItem($child, $level + 1);
        }
    }
}
