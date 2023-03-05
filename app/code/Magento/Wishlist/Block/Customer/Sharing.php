<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist customer sharing block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Customer;

use Magento\Captcha\Block\Captcha;
use Magento\Framework\Session\Generic;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Model\Config;

/**
 * Class Sharing
 *
 * @api
 * @since 100.0.2
 * @package Magento\Wishlist\Block\Customer
 */
class Sharing extends Template
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
     * @var Config
     */
    protected $_wishlistConfig;

    /**
     * @var Generic
     */
    protected $_wishlistSession;

    /**
     * @param Context $context
     * @param Config $wishlistConfig
     * @param Generic $wishlistSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $wishlistConfig,
        Generic $wishlistSession,
        array $data = []
    ) {
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
}
