<?php
/**
 * Datasource config interface.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Magento_Datasource_Config_Interface
{
    public function getClassByAlias($alias);
}
