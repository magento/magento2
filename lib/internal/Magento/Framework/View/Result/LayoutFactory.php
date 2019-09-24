<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Result;

use Magento\Framework\ObjectManagerInterface;

/**
 * @api
 *
 * @deprecated replaced with more generic ResultFactory
 * @see \Magento\Framework\Controller\ResultFactory::create
 */
class LayoutFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = Layout::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @return Layout
     */
    public function create()
    {
        /** @var Layout $resultLayout */
        $resultLayout = $this->objectManager->create($this->instanceName);
        $resultLayout->addDefaultHandle();
        return $resultLayout;
    }
}
