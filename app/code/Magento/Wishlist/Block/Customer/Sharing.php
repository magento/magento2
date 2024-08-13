<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Customer;

use Magento\Captcha\Block\Captcha;
use Magento\Framework\Validator\GlobalForbiddenPatterns;
use Magento\Framework\Exception\LocalizedException;

/**
 * Wishlist customer sharing block
 *
 * @api
 * @since 100.0.2
 */
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
     * @var GlobalForbiddenPatterns
     */
    protected $globalForbiddenPatterns;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Framework\Session\Generic $wishlistSession
     * @param GlobalForbiddenPatterns $globalForbiddenPatterns
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Framework\Session\Generic $wishlistSession,
        GlobalForbiddenPatterns $globalForbiddenPatterns,
        array $data = []
    ) {
        $this->globalForbiddenPatterns = $globalForbiddenPatterns;
        $this->_wishlistConfig = $wishlistConfig;
        $this->_wishlistSession = $wishlistSession;
        parent::__construct($context, $data);
    }

    /**
     * Prepare Global Layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        if (!$this->getChildBlock('captcha')) {
            $this->addChild(
                'captcha',
                Captcha::class,
                [
                    'cacheable' => false,
                    'after' => '-',
                    'form_id' => 'share_wishlist_form',
                    'image_width' => 230,
                    'image_height' => 230
                ]
            );
        }

        $this->pageConfig->getTitle()->set(__('Wish List Sharing'));
    }

    /**
     * Retrieve Send Form Action URL
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('wishlist/index/send');
    }

    /**
     * Retrieve Entered Data by key
     *
     * @param string $key
     * @return string|null
     */
    public function getEnteredData($key)
    {
        if ($this->_enteredData === null) {
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
        return $this->getUrl('wishlist');
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

    /**
     * Validate the sharing data (emails and message) against forbidden patterns
     *
     * @param string $emails
     * @param string $message
     * @return bool
     * @throws LocalizedException
     */
    public function validateSharingData($emails, $message)
    {
        // Validate the emails input
        if (!$this->globalForbiddenPatterns->validate($emails)) {
            throw new LocalizedException(__('The email addresses contain forbidden patterns.'));
        }

        // Validate the message input
        if (!$this->globalForbiddenPatterns->validate($message)) {
            throw new LocalizedException(__('The message contains forbidden patterns.'));
        }

        return true;
    }
}
