<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Result;

use Magento\Framework\ObjectManagerInterface;

/**
 * @api
 * @since 2.0.0
 */
class LayoutFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\View\Result\Layout::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function create()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->objectManager->create($this->instanceName);
        $resultLayout->addDefaultHandle();
        return $resultLayout;
    }
}
