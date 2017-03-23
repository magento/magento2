<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element\Iterator;

class Section extends \Magento\Config\Model\Config\Structure\Element\Iterator
{
    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Section $element
     */
    public function __construct(\Magento\Config\Model\Config\Structure\Element\Section $element)
    {
        parent::__construct($element);
    }
}
