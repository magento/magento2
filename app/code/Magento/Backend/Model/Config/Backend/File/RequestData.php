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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Backend\File;

class RequestData implements \Magento\Backend\Model\Config\Backend\File\RequestData\RequestDataInterface
{
    /**
     * Retrieve uploaded file tmp name by path
     *
     * @param string $path
     * @return string
     */
    public function getTmpName($path)
    {
        return $this->_getParam('tmp_name', $path);
    }

    /**
     * Retrieve uploaded file name by path
     *
     * @param string $path
     * @return string
     */
    public function getName($path)
    {
        return $this->_getParam('name', $path);
    }

    /**
     * Get $_FILES superglobal value by path
     *
     * @param string $paramName
     * @param string $path
     * @return string
     */
    protected function _getParam($paramName, $path)
    {
        $pathParts = explode('/', $path);
        array_shift($pathParts);
        $fieldId = array_pop($pathParts);
        $firstGroupId = array_shift($pathParts);
        if (!isset($_FILES['groups'][$paramName])) {
            return null;
        }
        $groupData = $_FILES['groups'][$paramName];
        if (isset($groupData[$firstGroupId])) {
            $groupData = $groupData[$firstGroupId];
        }
        foreach ($pathParts as $groupId) {
            if (isset($groupData['groups'][$groupId])) {
                $groupData = $groupData['groups'][$groupId];
            } else {
                return null;
            }
        }
        if (isset($groupData['fields'][$fieldId]['value'])) {
            return $groupData['fields'][$fieldId]['value'];
        }
        return null;
    }
}
