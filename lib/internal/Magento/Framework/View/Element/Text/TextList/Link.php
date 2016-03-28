<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Text\TextList;

use Magento\Framework\View\Element\Text;

/**
 * Class Link
 */
class Link extends \Magento\Framework\View\Element\Text
{
    /**
     * Set link
     *
     * @param array|string $liParams
     * @param array|string $aParams
     * @param string $innerText
     * @param string $afterText
     * @return $this
     */
    public function setLink($liParams, $aParams, $innerText, $afterText = '')
    {
        $this->setLiParams($liParams);
        $this->setAParams($aParams);
        $this->setInnerText($innerText);
        $this->setAfterText($afterText);

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

        $this->addText('><a');

        $params = $this->getAParams();
        if (!empty($params) && is_array($params)) {
            foreach ($params as $key => $value) {
                $this->addText(' ' . $key . '="' . addslashes($value) . '"');
            }
        } elseif (is_string($params)) {
            $this->addText(' ' . $params);
        }

        $this->addText('>' . $this->getInnerText() . '</a>' . $this->getAfterText() . '</li>' . "\r\n");

        return parent::_toHtml();
    }
}
