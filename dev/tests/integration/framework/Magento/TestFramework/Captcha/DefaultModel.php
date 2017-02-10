<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Captcha;

/**
 * Implementation of \Zend\Captcha\Image for integration test environment.
 *
 * With introducing "zendframework/zend-captcha": "~2.4.6" (see https://github.com/magento/magento2/pull/8356)
 * validation happens in \Zend\Captcha\Image class constructor that blocks testing on Travis CI, even for cases
 * when this functionality is not needed.
 * For test environment that supports freetype functional behaviour should not be changed.
 */
class DefaultModel extends \Magento\Captcha\Model\DefaultModel
{
    /**
     * Do not pass onto the parent constructor as imageftbbox is not initialized on travis
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     * @param string $formId
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Captcha\Helper\Data $captchaData,
        \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory,
        $formId
    ) {
        /**
         * Workaround for test environments that do not support freetype.
         */
        if (function_exists("imageftbbox")) {
            parent::__construct($session, $captchaData, $resLogFactory, $formId);
        } else {
            $this->session = $session;
            $this->captchaData = $captchaData;
            $this->resLogFactory = $resLogFactory;
            $this->formId = $formId;
        }
    }
}
