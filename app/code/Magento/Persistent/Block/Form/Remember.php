<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Block\Form;

/**
 * Remember Me block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Remember extends \Magento\Framework\View\Element\Template
{
    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData = null;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getRandomString($length, $chars = null)
    {
        return $this->mathRandom->getRandomString($length, $chars);
    }
}
