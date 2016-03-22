<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
