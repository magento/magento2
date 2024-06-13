<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Search\Model\Autocomplete;

class Item extends \Magento\Framework\DataObject implements ItemInterface
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }
}
