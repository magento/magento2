<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

/**
 * @api
 * @since 2.0.0
 */
class SectionConfig extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Config\DataInterface
     * @since 2.0.0
     */
    protected $sectionConfig;

    /**
     * Client side section.
     * Sections that do not have server side providers
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $clientSideSections;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Config\DataInterface $sectionConfig
     * @param array $data
     * @param string[] $clientSideSections
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Config\DataInterface $sectionConfig,
        array $data = [],
        array $clientSideSections = []
    ) {
        parent::__construct($context, $data);
        $this->sectionConfig = $sectionConfig;
        $this->clientSideSections = array_values($clientSideSections);
    }

    /**
     * Get list of sections for invalidation
     *
     * @return array
     * @since 2.0.0
     */
    public function getSections()
    {
        return $this->sectionConfig->get('sections');
    }

    /**
     * Get list of client side sections
     * @return string[]
     * @since 2.0.0
     */
    public function getClientSideSections()
    {
        return $this->clientSideSections;
    }
}
