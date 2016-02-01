<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Block\Adminhtml\Js;

/**
 * Block Session Checker
 */
class Checker extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Retrieve session checker data in JSON format
     *
     * @return string
     */
    public function getSessionCheckerJson()
    {
        return $this->jsonEncoder->encode(
            [
                'requestUrl' => $this->getUrl('security/session/check'),
                'redirectUrl' => $this->getUrl('adminhtml/')
            ]
        );
    }
}
