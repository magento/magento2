<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend\File;

/**
 * Class \Magento\Config\Model\Config\Backend\File\RequestData
 *
 * @since 2.0.0
 */
class RequestData implements \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface
{
    /**
     * Retrieve uploaded file tmp name by path
     *
     * @param string $path
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
