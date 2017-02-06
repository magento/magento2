<?php
/**
 * Captcha model factory
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model;

class CaptchaFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get captcha instance
     *
     * @param string $captchaType
     * @param string $formId
     * @return \Magento\Captcha\Model\CaptchaInterface
     * @throws \InvalidArgumentException
     */
    public function create($captchaType, $formId)
    {
        $className = 'Magento\Captcha\Model\\' . ucfirst($captchaType);

        $instance = $this->_objectManager->create($className, ['formId' => $formId]);
        if (!$instance instanceof \Magento\Captcha\Model\CaptchaInterface) {
            throw new \InvalidArgumentException(
                $className . ' does not implement \Magento\Captcha\Model\CaptchaInterface'
            );
        }
        return $instance;
    }
}
