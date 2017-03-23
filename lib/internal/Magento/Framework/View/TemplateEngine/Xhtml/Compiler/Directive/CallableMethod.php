<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive;

use Magento\Framework\DataObject;

/**
 * Class CallableMethod
 */
class CallableMethod implements DirectiveInterface
{
    /**
     * Execute directive
     *
     * @param array $directive
     * @param DataObject $processedObject
     * @return string
     */
    public function execute($directive, DataObject $processedObject)
    {
        $object = $processedObject;
        $result = '';
        foreach (explode('.', $directive[1]) as $method) {
            $methodName = substr($method, 0, strpos($method, '('));
            if (is_callable([$object, $methodName])) {
                $result = $object->$methodName();
                if (is_scalar($result)) {
                    break;
                }
                $object = $result;
                continue;
            }
            break;
        }

        return $result;
    }

    /**
     * Get regexp search pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return '#\{\{((?:[\w_0-9]+\(\)){1}(?:(?:\.[\w_0-9]+\(\))+)?)\}\}#';
    }
}
