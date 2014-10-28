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


/**
 * Wishlist customer sharing block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Customer;

class Sharing extends \Magento\Framework\View\Element\Template
{
    /**
     * Entered Data cache
     *
     * @var array|null
     */
    protected $_enteredData = null;

    /**
     * Wishlist configuration
     *
     * @var \Magento\Wishlist\Model\Config
     */
    protected $_wishlistConfig;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_wishlistSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Framework\Session\Generic $wishlistSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Framework\Session\Generic $wishlistSession,
        array $data = array()
    ) {
        $this->_wishlistConfig = $wishlistConfig;
        $this->_wishlistSession = $wishlistSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Prepare Global Layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->setTitle(__('Wish List Sharing'));
    }

    /**
     * Retrieve Send Form Action URL
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('*/*/send');
    }

    /**
     * Retrieve Entered Data by key
     *
     * @param string $key
     * @return string|null
     */
    public function getEnteredData($key)
    {
        if (is_null($this->_enteredData)) {
            $this->_enteredData = $this->_wishlistSession->getData('sharing_form', true);
        }

        if (!$this->_enteredData || !isset($this->_enteredData[$key])) {
            return null;
        } else {
            return $this->escapeHtml($this->_enteredData[$key]);
        }
    }

    /**
     * Retrieve back button url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * Retrieve number of emails allowed for sharing
     *
     * @return int
     */
    public function getEmailSharingLimit()
    {
        return $this->_wishlistConfig->getSharingEmailLimit();
    }

    /**
     * Retrieve maximum email length allowed for sharing
     *
     * @return int
     */
    public function getTextSharingLimit()
    {
        return $this->_wishlistConfig->getSharingTextLimit();
    }
}
