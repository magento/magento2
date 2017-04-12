<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Block\Form;

/**
 * Remember Me block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Remember extends \Magento\Framework\View\Element\Template
{
    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData = null;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Framework\Math\Random $mathRandom,
        array $data = []
    ) {
        $this->_persistentData = $persistentData;
        $this->mathRandom = $mathRandom;
        parent::__construct($context, $data);
    }

    /**
     * Prevent rendering if Persistent disabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_persistentData->isEnabled() &&
            $this->_persistentData->isRememberMeEnabled() ? parent::_toHtml() : '';
    }

    /**
     * Is "Remember Me" checked
     *
     * @return bool
     */
    public function isRememberMeChecked()
    {
        return $this->_persistentData->isEnabled() &&
            $this->_persistentData->isRememberMeEnabled() &&
            $this->_persistentData->isRememberMeCheckedDefault();
    }

    /**
     * Get random string
     *
     * @param int $length
     * @param string|null $chars
     * @return string
     * @codeCoverageIgnore
     */
    public function getRandomString($length, $chars = null)
    {
        return $this->mathRandom->getRandomString($length, $chars);
    }
}
