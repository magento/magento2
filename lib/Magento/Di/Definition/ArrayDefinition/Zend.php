<?php
/**
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
 * @category    Magento
 * @package     Magento_Di
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Di_Definition_ArrayDefinition_Zend extends Zend\Di\Definition\ArrayDefinition
    implements Magento_Di_Definition_ArrayDefinition
{
    /**
     * @param array $dataArray
     */
    public function __construct(Array $dataArray)
    {
        $this->dataArray = $dataArray;
    }

    /**
     * Check whether definition contains class
     *
     * @param string $class
     * @return bool
     */
    public function hasClass($class)
    {
        $result = array_key_exists($class, $this->dataArray);
        if ($result && !is_array($this->dataArray[$class])) {
            $this->dataArray[$class] = json_decode($this->dataArray[$class], true);
        }
        return $result;
    }
}
