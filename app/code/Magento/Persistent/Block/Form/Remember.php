<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        array $data = array()
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
     */
    public function getRandomString($length, $chars = null)
    {
        return $this->mathRandom->getRandomString($length, $chars);
    }
}
