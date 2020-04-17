<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Frontend form key content block
 */
namespace Magento\Cookie\Block\Html;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Cookie\Helper\Cookie;

/**
 * @api
 * @since 100.0.2
 */
class Notices extends \Magento\Framework\View\Element\Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        $data['cookieHelper'] = ObjectManager::getInstance()->get(Cookie::class);
        parent::__construct($context, $data);
    }

    /**
     * Get Link to cookie restriction privacy policy page
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPrivacyPolicyLink()
    {
        return $this->_urlBuilder->getUrl('privacy-policy-cookie-restriction-mode');
    }
}
