<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Page;

/**
 * Adminhtml footer block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Footer extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::page/footer.phtml';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setShowProfiler(true);
    }

    /**
     * Get product version
     *
     * @return string
     * @since 100.1.0
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
