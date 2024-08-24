<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Customer;

use Magento\Captcha\Block\Captcha;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Validator\GlobalForbiddenPatterns;
use Magento\Store\Model\ScopeInterface;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GlobalForbiddenPatterns
     */
    private $forbiddenPatternsValidator;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Framework\Session\Generic $wishlistSession
     * @param ScopeConfigInterface $scopeConfig
     * @param GlobalForbiddenPatterns $forbiddenPatternsValidator
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Framework\Session\Generic $wishlistSession,
        ScopeConfigInterface $scopeConfig,
        GlobalForbiddenPatterns $forbiddenPatternsValidator,
        array $data = []
    ) {
        $this->_wishlistConfig = $wishlistConfig;
        $this->_wishlistSession = $wishlistSession;
        $this->scopeConfig = $scopeConfig;
        $this->forbiddenPatternsValidator = $forbiddenPatternsValidator;
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
        }

        $value = $this->_enteredData[$key];

        // Check if regex validation is enabled
        $isRegexEnabled = $this->scopeConfig->isSetFlag(
            'system/security/security_regex_enabled',
            ScopeInterface::SCOPE_STORE
        );

        // Perform regex validation
        if ($isRegexEnabled && is_string($value)) {
            if (!$this->forbiddenPatternsValidator->isValid($value)) {
                return null; // or throw an exception or return a sanitized value
            }
        }

        return $this->escapeHtml($value);
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
}
