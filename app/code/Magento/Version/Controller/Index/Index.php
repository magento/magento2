<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
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
        $versionParts = explode('.', $this->productMetadata->getVersion());
        if (!isset($versionParts[0]) || !isset($versionParts[1])) {
            return ; // Major and minor version are not set - return empty response
        }
        $majorMinorVersion = $versionParts[0] . '.' . $versionParts[1];
        $this->getResponse()->setBody(
            $this->productMetadata->getName() . '/' .
            $majorMinorVersion . ' (' .
            $this->productMetadata->getEdition() . ')'
        );
    }
}
