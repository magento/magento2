<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Captcha;

/**
 * Implementation of \Zend\Captcha\Image
 *
 * @author     Magento Core Team <core@magentocommerce.com>
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
        $this->session = $session;
        $this->captchaData = $captchaData;
        $this->resLogFactory = $resLogFactory;
        $this->formId = $formId;
    }
}