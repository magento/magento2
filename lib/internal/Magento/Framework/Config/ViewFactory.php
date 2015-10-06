<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config;

class ViewFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $configFiles
     * @return View
     */
    public function create($configFiles)
    {
        return new \Magento\Framework\Config\View(
            $configFiles,
            new \Magento\Framework\Config\Dom\UrnResolver(),
            $this->objectManager->create('\Magento\Framework\View\Xsd\Media\TypeDataExtractorPool')
        );
    }
}
