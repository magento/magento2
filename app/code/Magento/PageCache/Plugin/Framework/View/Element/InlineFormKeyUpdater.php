<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Magento\PageCache\Plugin\Framework\View\Element;


class InlineFormKeyUpdater
{
    public function afterToHtml(\Magento\Framework\View\Element\FormKey $subject, $result)
    {
        return $result.$this->getInlineJavaScript();
    }
    
    private function getInlineJavaScript()
    {
        $uniqueId = uniqid();
        
        return '<script id="'.$uniqueId.'">document.getElementById("'.$uniqueId.'").previousSibling.value = formKey;</script>';
    }
}
?>
