<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Helper;

use \Magento\Framework\ObjectManager\Helper\Composite;

use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeHelper
     */
    protected $compositeHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->compositeHelper = $this->objectManager->getObject(
            \Magento\Framework\ObjectManager\Helper\Composite::class
        );
    }

    public function testFilterAndSortDeclaredComponents()
    {
        $firstComponent = new \Magento\Framework\DataObject();
        $secondComponent = new \Magento\Framework\DataObject();
        $thirdComponent = new \Magento\Framework\DataObject();
        $contexts = [
            [
                'type' => new \Magento\Framework\DataObject(),
            ],
            [
                'sortOrder' => 50,
            ],
            [
                'sortOrder' => 20,
                'type' => $firstComponent
            ],
            [
                'sortOrder' => 30,
                'type' => $secondComponent,
            ],
            [
                'sortOrder' => 10,
                'type' => $thirdComponent
            ],
        ];

        $result = $this->compositeHelper->filterAndSortDeclaredComponents($contexts);

        /** Ensure that misconfigured components were filtered out correctly */
        $this->assertCount(3, $result, 'Misconfigured components filtration does not work as expected.');

        /** Verify that components were ordered according to the defined sort order */
        $incorrectSortingMessage = "Registered components were sorted incorrectly";
        $this->assertSame($thirdComponent, $result[0]['type'], $incorrectSortingMessage);
        $this->assertSame($firstComponent, $result[1]['type'], $incorrectSortingMessage);
        $this->assertSame($secondComponent, $result[2]['type'], $incorrectSortingMessage);
    }
}
