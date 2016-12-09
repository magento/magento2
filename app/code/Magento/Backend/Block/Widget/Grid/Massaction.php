<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

class Massaction extends \Magento\Backend\Block\Widget\Grid\Massaction\AbstractMassaction
{
    /**
     * @var State
     */
    private $state;

    /**
     * {@inheritdoc}
     */
    public function addItem($itemId, $item)
    {
        if (is_array($item)) {
            $item = new DataObject($item);
        }

        if ($item->getData('hide_in_production') === true
            && $this->getState()->getMode() === State::MODE_PRODUCTION
        ) {
            return $this;
        }

        return parent::addItem($itemId, $item);
    }

    /**
     * Get State Instance
     *
     * @return State
     * @deprecated
     */
    private function getState()
    {
        if ($this->state === null) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }

        return $this->state;
    }
}
