<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfiguration\Test\Unit\Model\SourceItemConfiguration;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class GetSourceItemConfiguration extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\InventoryConfiguration\Model\SourceItemConfiguration\GetSourceItemConfiguration */
    private $sourceItemConfigurationModel;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->sourceItemConfigurationModel = $this->objectManagerHelper->getObject(
            \Magento\InventoryConfiguration\Model\SourceItemConfiguration\GetSourceItemConfiguration::class
        );
    }
}
