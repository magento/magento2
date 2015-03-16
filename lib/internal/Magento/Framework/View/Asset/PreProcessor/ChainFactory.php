<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\ObjectManagerInterface;

class ChainFactory implements ChainFactoryInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * {inheritdoc}
     */
    public function create(array $arguments = [])
    {
        $arguments = array_intersect_key(
            $arguments,
            [
                'asset' => 'asset',
                'origContent' => 'origContent',
                'origContentType' => 'origContentType'
            ]
        );
        return $this->_objectManager->create('Magento\Framework\View\Asset\PreProcessor\Chain', $arguments);
    }
}
