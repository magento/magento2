<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\ViewModel\Address;

use Magento\Directory\Model\RegionProvider as DirectoryRegionProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RegionProvider implements ArgumentInterface
{

    /**
     * @var DirectoryRegionProvider
     */
    private $directoryRegionProvider;

    /**
     * Constructor
     *
     * @param DirectoryRegionProvider $directoryRegionProvider
     */
    public function __construct(
        DirectoryRegionProvider $directoryRegionProvider
    ) {
        $this->directoryRegionProvider = $directoryRegionProvider;
    }

    public function getRegionJson(): string
    {
        return $this->directoryRegionProvider->getRegionJson();
    }
}
