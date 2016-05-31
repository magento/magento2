<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $data['aggregations'] = $this->processData($requestData['aggregations'], $bindData['placeholder']);

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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
        array_walk_recursive($bindData, function (&$item) {
            $item = trim($item);
        });
        $bindData = array_filter($bindData, function ($element) {
            return is_array($element) ? count($element) : strlen($element);
        });

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->processData($value, $bindData);
            } else {
                foreach ($bindData as $bindKey => $bindValue) {
                    if (strpos($value, $bindKey) !== false) {
                        if (is_string($bindValue)) {
                            $data[$key] = str_replace($bindKey, $bindValue, $value);
                        } else {
                            $data[$key] = $bindValue;
                        }
                        $data['is_bind'] = true;
                    }
                }
            }
        }

        return $data;
    }
}
