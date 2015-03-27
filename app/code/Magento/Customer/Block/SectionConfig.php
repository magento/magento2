<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

/**
 * Section Config
 */
class SectionConfig extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Framework\Config\DataInterface */
    protected $sectionConfig;

    /** @var \Magento\Framework\Json\EncoderInterface */
    protected $jsonEncoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Config\DataInterface $sectionConfig
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Config\DataInterface $sectionConfig,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sectionConfig = $sectionConfig;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Get list of sections for invalidation
     *
     * @return array
     */
    public function getSections()
    {
        return $this->jsonEncoder->encode($this->sectionConfig->get('sections'));
    }
}
