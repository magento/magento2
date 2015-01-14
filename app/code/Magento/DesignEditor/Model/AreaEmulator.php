<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model;

class AreaEmulator
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param string $areaCode
     * @return void
     */
    public function emulateLayoutArea($areaCode)
    {
        $configuration = [
            'Magento\Framework\View\Layout' => [
                'arguments' => [
                    'area' => $areaCode,
                ],
            ],
        ];
        $this->_objectManager->configure($configuration);
    }
}
