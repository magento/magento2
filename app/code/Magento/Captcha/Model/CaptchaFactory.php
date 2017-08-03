<?php
/**
 * Captcha model factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model;

/**
 * Class \Magento\Captcha\Model\CaptchaFactory
 *
 * @since 2.0.0
 */
class CaptchaFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
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
