<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Dhl\Model\Validator;

/**
 * Performs XML and string processing for errors produced by the DHL shipping service
 */
class ResponseErrorProcessor
{
    /**
     * Processes error encountered in DHL XML response
     *
     * @param \SimpleXMLElement $xml
     * @param bool $isShippingLabel
     * @return \Magento\Framework\Phrase
     */
    public function process($xml, $isShippingLabel)
    {
        $code = null;
        $data = null;
        $nodeCondition = isset($xml->Response->Status->Condition)
            ? $xml->Response->Status->Condition : $xml->GetQuoteResponse->Note->Condition;

        if ($isShippingLabel) {
            foreach ($nodeCondition as $condition) {
                $code = (string)$condition->ConditionCode;
                $data = (string)$condition->ConditionData;
                if (!empty($code) && !empty($data)) {
                    break;
                }
            }
            return __('Error #%1 : %2', trim($code), trim($data));
        }

        $code = (string)$nodeCondition->ConditionCode ?: 0;
        $data = (string)$nodeCondition->ConditionData ?: '';
        return __('Error #%1 : %2', trim($code), trim($data));
    }
}
