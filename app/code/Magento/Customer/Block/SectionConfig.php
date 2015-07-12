<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

class SectionConfig extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Framework\Config\DataInterface */
    protected $sectionConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Config\DataInterface $sectionConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Config\DataInterface $sectionConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sectionConfig = $sectionConfig;
    }

    /**
     * Get list of sections for invalidation
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sectionConfig->get('sections');
    }
}
