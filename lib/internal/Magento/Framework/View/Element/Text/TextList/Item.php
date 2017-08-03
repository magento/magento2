<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Text\TextList;

use Magento\Framework\View\Element\Text;

/**
 * Class Item
 * @since 2.0.0
 */
class Item extends \Magento\Framework\View\Element\Text
{
    /**
     * Set link
     *
     * @param array|string $liParams
     * @param string $innerText
     * @return $this
     * @since 2.0.0
     */
    public function setLink($liParams, $innerText)
    {
        $this->setLiParams($liParams);
        $this->setInnerText($innerText);

        return $this;
    }

    /**
     * Render html output
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $this->setText('<li');

        $params = $this->getLiParams();
        if (!empty($params) && is_array($params)) {
            foreach ($params as $key => $value) {
                $this->addText(' ' . $key . '="' . addslashes($value) . '"');
            }
        } elseif (is_string($params)) {
            $this->addText(' ' . $params);
        }

        $this->addText('>' . $this->getInnerText() . '</li>' . "\r\n");

        return parent::_toHtml();
    }
}
