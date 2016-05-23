<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Text\TextList;

use Magento\Framework\View\Element\Text;

/**
 * Class Item
 */
class Item extends \Magento\Framework\View\Element\Text
{
    /**
     * Set link
     *
     * @param array|string $liParams
     * @param string $innerText
     * @return $this
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
