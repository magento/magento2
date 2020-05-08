<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Helper;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    /**
     * @var CompositeHelper
     */
    protected $compositeHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->compositeHelper = $this->objectManager->getObject(
            \Magento\Framework\ObjectManager\Helper\Composite::class
        );
    }

    public function testFilterAndSortDeclaredComponents()
    {
        $firstComponent = new DataObject();
        $secondComponent = new DataObject();
        $thirdComponent = new DataObject();
        $contexts = [
            [
                'type' => new DataObject(),
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
