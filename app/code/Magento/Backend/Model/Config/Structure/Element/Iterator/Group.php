<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure\Element\Iterator;

class Group extends \Magento\Backend\Model\Config\Structure\Element\Iterator
{
    /**
     * @param \Magento\Backend\Model\Config\Structure\Element\Group $element
     */
    public function __construct(\Magento\Backend\Model\Config\Structure\Element\Group $element)
    {
        parent::__construct($element);
    }
}
