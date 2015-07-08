<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class DataFactory
 */
class DataFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $helperMap = [
        'frontend' => 'Magento\Authorizenet\Helper\Data',
        'adminhtml' => 'Magento\Authorizenet\Helper\Backend\Data'
    ];

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create data helper
     *
     * @param string $area
     * @return \Magento\Authorizenet\Helper\Backend\Data|\Magento\Authorizenet\Helper\Data
     * @throws LocalizedException
     */
    public function create($area)
    {
        if (!isset($this->helperMap[$area])) {
            throw new LocalizedException(__(sprintf('For this area <%s> no suitable helper', $area)));
        }

        return $this->objectManager->get($this->helperMap[$area]);
    }
}
