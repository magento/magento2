<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Pricing\Render;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Weee\Helper\Data;
use Magento\Weee\Pricing\Adjustment;
use Magento\Weee\Pricing\Render\TaxAdjustment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxAdjustmentTest extends TestCase
{
    /**
     * @var \Magento\Weee\Pricing\Render\TaxAdjustment
     */
    protected $model;

    /**
     * Weee helper mock
     *
     * @var \Magento\Weee\Helper\Data|MockObject
     */
    protected $weeeHelperMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks and model
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->weeeHelperMock = $this->createPartialMock(
            Data::class,
            ['typeOfDisplay', 'isTaxable']
        );

        $this->model = $this->objectManager->getObject(
            TaxAdjustment::class,
            [
                'weeeHelper' => $this->weeeHelperMock,
            ]
        );
    }

    /**
     * Test for method getDefaultExclusions
     *
     * @dataProvider getDefaultExclusionsDataProvider
     */
    public function testGetDefaultExclusions($weeeIsExcluded)
    {
        //setup
        $this->weeeHelperMock->expects($this->atLeastOnce())->method('typeOfDisplay')->willReturn($weeeIsExcluded);

        //test
        $defaultExclusions = $this->model->getDefaultExclusions();
        $this->assertNotEmpty($defaultExclusions, 'Expected to have at least one default exclusion: tax');

        $taxCode = $this->model->getAdjustmentCode(); // since Weee's TaxAdjustment is a subclass of Tax's Adjustment
        $this->assertContains($taxCode, $defaultExclusions);

        $weeeCode = Adjustment::ADJUSTMENT_CODE;
        if ($weeeIsExcluded) {
            $this->assertContains($weeeCode, $defaultExclusions);
        } else {
            $this->assertNotContains($weeeCode, $defaultExclusions);
        }
    }

    /**
     * Data provider for testGetDefaultExclusions()
     * @return array
     */
    public function getDefaultExclusionsDataProvider()
    {
        return [
            'weee part of exclusions' => [true],
            'weee not part of exclusions' => [false],
        ];
    }
}
