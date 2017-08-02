<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\Autocomplete;

/**
 * Class \Magento\Search\Model\Autocomplete\Item
 *
 * @since 2.0.0
 */
class Item extends \Magento\Framework\DataObject implements ItemInterface
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }
}
