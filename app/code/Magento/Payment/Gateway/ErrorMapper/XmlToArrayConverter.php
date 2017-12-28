<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\ErrorMapper;

use Magento\Framework\Config\ConverterInterface;

/**
 * Reads xml in `<message code="code">message</message>` format and converts it to [code => message] array format.
 */
class XmlToArrayConverter implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convert($source)
    {
        $result = [];
        $messageList = $source->getElementsByTagName('message');
        foreach ($messageList as $messageNode) {
            $result[(string) $messageNode->getAttribute('code')] = (string) $messageNode->nodeValue;
        }
        return $result;
    }
}
