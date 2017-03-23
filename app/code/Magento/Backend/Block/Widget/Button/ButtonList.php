<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Button;

class ButtonList
{
    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @param ItemFactory $itemFactory
     */
    public function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    /**
     * @var array
     */
    protected $_buttons = [-1 => [], 0 => [], 1 => []];

    /**
     * Add a button
     *
     * @param string $buttonId
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $region That button should be displayed in ('toolbar', 'header', 'footer', null)
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function add($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if (!isset($this->_buttons[$level])) {
            $this->_buttons[$level] = [];
        }

        $data['id'] = empty($data['id']) ? $buttonId : $data['id'];
        $data['button_key'] = $data['id'] . '_button';
        $data['region'] = empty($data['region']) ? $region : $data['region'];
        $data['level'] = $level;
        $sortOrder = $sortOrder ?: (count($this->_buttons[$level]) + 1) * 10;
        $data['sort_order'] = empty($data['sort_order']) ? $sortOrder : $data['sort_order'];
        $this->_buttons[$level][$buttonId] = $this->itemFactory->create(['data' => $data]);
    }

    /**
     * Remove existing button
     *
     * @param string $buttonId
     * @return void
     */
    public function remove($buttonId)
    {
        foreach ($this->_buttons as $level => $buttons) {
            if (isset($buttons[$buttonId])) {
                /** @var Item $item */
                $item = $buttons[$buttonId];
                $item->isDeleted(true);
                unset($this->_buttons[$level][$buttonId]);
            }
        }
    }

    /**
     * Update specified button property
     *
     * @param string $buttonId
     * @param string|null $key
     * @param string $data
     * @return void
     */
    public function update($buttonId, $key, $data)
    {
        foreach ($this->_buttons as $level => $buttons) {
            if (isset($buttons[$buttonId])) {
                if (!empty($key)) {
                    if ('level' == $key) {
                        $this->_buttons[$data][$buttonId] = $this->_buttons[$level][$buttonId];
                        unset($this->_buttons[$level][$buttonId]);
                    } else {
                        /** @var Item $item */
                        $item = $this->_buttons[$level][$buttonId];
                        $item->setData($key, $data);
                    }
                } else {
                    /** @var Item $item */
                    $item = $this->_buttons[$level][$buttonId];
                    $item->setData($data);
                }
                break;
            }
        }
    }

    /**
     * Get all buttons
     *
     * @return array
     */
    public function getItems()
    {
        array_walk($this->_buttons, function (&$item) {
            uasort($item, [$this, 'sortButtons']);
        });
        return $this->_buttons;
    }

    /**
     * Sort buttons by sort order
     *
     * @param Item $itemA
     * @param Item $itemB
     * @return int
     */
    public function sortButtons(Item $itemA, Item $itemB)
    {
        $sortOrderA = intval($itemA->getSortOrder());
        $sortOrderB = intval($itemB->getSortOrder());

        if ($sortOrderA == $sortOrderB) {
            return 0;
        }
        return ($sortOrderA < $sortOrderB) ? -1 : 1;
    }
}
