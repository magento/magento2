<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
