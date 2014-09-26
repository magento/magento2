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
namespace Magento\Framework\Search\Request;

class Binder
{
    /**
     * Bind data to request data
     *
     * @param array $requestData
     * @param array $bindData
     * @return array
     */
    public function bind(array $requestData, array $bindData)
    {
        $data = $this->processLimits($requestData, $bindData);
        $data['dimensions'] = $this->processDimensions($requestData['dimensions'], $bindData['dimensions']);
        $data['queries'] = $this->processData($requestData['queries'], $bindData['placeholder']);
        $data['filters'] = $this->processData($requestData['filters'], $bindData['placeholder']);

        return $data;
    }

    /**
     * Replace bind limits
     *
     * @param array $data
     * @param array $bindData
     * @return array
     */
    private function processLimits($data, $bindData)
    {
        $limitList = ['from', 'size'];
        foreach ($limitList as $limit) {
            if (isset($bindData[$limit])) {
                $data[$limit] = $bindData[$limit];
            }
        }
        return $data;
    }

    /**
     * @param array $data
     * @param array $bindData
     * @return array
     */
    private function processDimensions($data, $bindData)
    {
        foreach ($data as $name => $value) {
            if (isset($bindData[$name])) {
                $data[$name]['value'] = $bindData[$name];
            }
        }
        return $data;
    }

    /**
     * Replace data recursive
     *
     * @param array $data
     * @param array $bindData
     * @return array
     */
    private function processData($data, $bindData)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->processData($value, $bindData);
            } elseif (!empty($bindData[$value])) {
                $data[$key] = $bindData[$value];
            }
        }
        return $data;
    }
}
