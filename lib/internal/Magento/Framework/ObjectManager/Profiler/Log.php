<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    protected $data = [];

    /**
     * @var array
     */
    protected $roots = [];

    /**
     * @var array
     */
    protected $used = [];

    /**
     * @var array
     */
    protected $stats = ['total' => 0, 'used' => 0, 'unused' => 0];

    /**
     * Constructor
     */
    public function __construct()
    {
        register_shutdown_function([$this, 'display']);
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
        echo '<table border="1" cellspacing="0" cellpadding="2">',
            '<thead><tr><th>',
            "Creation chain (Red items are never used) Total: {$this->stats['total']}\n",
            "Used: {$this->stats['used']} Not used: {$this->stats['unused']}",
            '</th></tr></thead>',
            '<tbody>',
            '<tr><th>Instance class</th></tr>';
        foreach ($this->roots as $root) {
            $this->displayItem($root);
        }
        echo '</tbody></table>';
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
