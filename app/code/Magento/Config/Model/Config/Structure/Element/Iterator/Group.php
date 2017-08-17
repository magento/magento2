<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element\Iterator;

/**
 * @api
 */
class Group extends \Magento\Config\Model\Config\Structure\Element\Iterator
{
    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Group $element
     */
    public function __construct(\Magento\Config\Model\Config\Structure\Element\Group $element)
    {
        parent::__construct($element);
    }
}
