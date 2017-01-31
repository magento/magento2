<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Result;

use Magento\Framework\ObjectManagerInterface;

/**
 * @api
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
        $instanceName = 'Magento\Framework\View\Result\Layout'
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function create()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->objectManager->create($this->instanceName);
        $resultLayout->addDefaultHandle();
        return $resultLayout;
    }
}
