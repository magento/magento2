<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Element\Text;

use Magento\Framework\View\Element\Text;

/**
 * Class ListText
 */
class ListText extends \Magento\Framework\View\Element\Text
{
    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setText('');

        $layout = $this->getLayout();
        foreach ($this->getChildNames() as $child) {
            $this->addText($layout->renderElement($child, false));
        }

        return parent::_toHtml();
    }
}
