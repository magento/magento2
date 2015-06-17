<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Version\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Magento Version controller
 */
class Index extends Action
{
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(Context $context, ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * Sets the response body to ProductName/Major.MinorVersion (Edition). E.g.: Magento/0.42 (Community). Omits patch
     * version from response
     *
     * @return void
     */
    public function execute()
    {
        $fullVersion = explode('.', $this->productMetadata->getVersion());
        $majorMinorVersion = $fullVersion[0] . '.' . $fullVersion[1];
        $this->getResponse()->setBody(
            $this->productMetadata->getName() . '/' .
            $majorMinorVersion . ' (' .
            $this->productMetadata->getEdition() . ')'
        );
    }
}
