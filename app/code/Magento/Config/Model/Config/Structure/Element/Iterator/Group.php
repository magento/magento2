<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Config\Model\Config\Structure\Element\Iterator;

/**
 * @api
 * @since 100.0.2
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
