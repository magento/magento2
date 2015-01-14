<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure\Element\Iterator;

class Tab extends \Magento\Backend\Model\Config\Structure\Element\Iterator
{
    /**
     * @param \Magento\Backend\Model\Config\Structure\Element\Tab $element
     */
    public function __construct(\Magento\Backend\Model\Config\Structure\Element\Tab $element)
    {
        parent::__construct($element);
    }
}
