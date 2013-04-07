<?php
/**
 * Data source visitable interface
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Magento_Datasource_Path_Visitable
{
    public function visit(Magento_Datasource_Path_Visitor $visitor);
}