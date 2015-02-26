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
     * Sets the response body with ProductName/Version (Edition). E.g.: Magento/0.42.0-beta3 (Community)
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->productMetadata->getName() . '/' .
            $this->productMetadata->getVersion() . ' (' .
            $this->productMetadata->getEdition() . ')'
        );
    }
}
