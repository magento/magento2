<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure\Element\Iterator;

class Section extends \Magento\Backend\Model\Config\Structure\Element\Iterator
{
    /**
     * @param \Magento\Backend\Model\Config\Structure\Element\Section $element
     */
    public function __construct(\Magento\Backend\Model\Config\Structure\Element\Section $element)
    {
        parent::__construct($element);
    }
}
