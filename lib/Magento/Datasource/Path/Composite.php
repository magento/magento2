<?php
/**
 * Data source composite visitable element
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Datasource_Path_Composite implements Magento_Datasource_Path_Visitable
{
    protected $_children = array();

    public function __construct(Magento_ObjectManager $objectManager, $items)
    {
        foreach ($items as $key => $item) {
            $this->_children[$key] = $objectManager->create($item);
        }
    }

    public function visit(Magento_Datasource_Path_Visitor $visitor)
    {
        $result = $visitor->visitArray($this->_children);
        return $result;
    }
}