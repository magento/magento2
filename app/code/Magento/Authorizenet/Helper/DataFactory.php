<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class DataFactory
 * @since 2.0.0
 */
class DataFactory
{
    const AREA_FRONTEND = 'frontend';
    const AREA_BACKEND = 'adminhtml';

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $helperMap = [
        self::AREA_FRONTEND => \Magento\Authorizenet\Helper\Data::class,
        self::AREA_BACKEND => \Magento\Authorizenet\Helper\Backend\Data::class
    ];

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($area)
    {
        if (!isset($this->helperMap[$area])) {
            throw new LocalizedException(__(sprintf('For this area <%s> no suitable helper', $area)));
        }

        return $this->objectManager->get($this->helperMap[$area]);
    }
}
