<?php
/**
 * Converted XML to ACL builder array format mapper.
 * Translates array retrieved from xml array converter to format consumed by acl builder
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 * 
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Acl_Loader_Resource_ConfigReader_Xml_ArrayMapper
{
    /**
     * Map configuration
     *
     * @param array $xmlAsArray
     * @return array
     */
    public function map(array $xmlAsArray)
    {
        $result = array();
        foreach ($xmlAsArray as $item) {
            $resultItem = $item['__attributes__'];
            if (isset($resultItem['disabled']) && ($resultItem['disabled'] == 1 || $resultItem['disabled'] == 'true')) {
                continue;
            }
            unset($resultItem['disabled']);
            $resultItem['sortOrder'] = isset($resultItem['sortOrder']) ? $resultItem['sortOrder'] : 0;
            if (isset($item['resource'])) {
                $resultItem['children'] = $this->map($item['resource']);
            }
            $result[] = $resultItem;
        }
        usort($result, array($this, '_sortTree'));
        return $result;
    }

    /**
     * Sort ACL resource nodes
     *
     * @param array $nodeA
     * @param array $nodeB
     * @return int
     */
    protected function _sortTree(array $nodeA, array $nodeB)
    {
        return $nodeA['sortOrder'] < $nodeB['sortOrder'] ? -1 : ($nodeA['sortOrder'] > $nodeB['sortOrder'] ? 1 : 0);
    }
}
