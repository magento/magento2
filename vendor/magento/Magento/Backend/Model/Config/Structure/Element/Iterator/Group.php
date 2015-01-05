<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
