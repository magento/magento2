<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Controller\Adminhtml\Product\Attribute\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\DataObject;

/**
 * Class Save
 * @package Magento\Swatches\Controller\Adminhtml\Product\Attribute\Plugin
 */
class Validate
{
    /**
     * @param Attribute\Validate $subject
     * @param Json $response
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Attribute\Validate $subject, Json $response)
    {
        $data = $subject->getRequest()->getPostValue();
        if (isset($data['frontend_input'])) {
            $dataIndex = false;
            switch ($data['frontend_input']) {
                case "select":
                    $dataIndex = "option";
                    break;
                case "swatch_text":
                    $dataIndex = "optiontext";
                    break;
                case "swatch_visual":
                    $dataIndex = "optionvisual";
                    break;
            }

            if ($dataIndex !== false) {
                if (!$this->isUniqueAdminValues($data[$dataIndex]['value'], $data[$dataIndex]['delete'])) {
                    $response->setJsonData($this->createErrorResponse("The value of Admin must be unique.")->toJson());
                };
            }
        }

        return $response;
    }

    /**
     * Throws Exception if not unique values into options
     * @param array $optionsValues
     * @param array $deletedOptions
     * @return bool
     */
    private function isUniqueAdminValues(array $optionsValues, array $deletedOptions)
    {
        $adminValues = [];
        foreach ($optionsValues as $optionKey => $values) {
            if (!(isset($deletedOptions[$optionKey]) and $deletedOptions[$optionKey] === '1')) {
                $adminValues[] = reset($values);
            }
        }
        $uniqueValues = array_unique($adminValues);
        return ($uniqueValues === $adminValues);
    }

    /**
     * @param string $message
     * @return DataObject
     */
    private function createErrorResponse($message)
    {
        $error = new DataObject();
        $error->setError(true);
        $error->setData(Attribute\Validate ::DEFAULT_MESSAGE_KEY, __($message));
        return $error;
    }
}
