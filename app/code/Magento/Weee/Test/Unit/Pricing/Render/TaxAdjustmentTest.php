<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Unit\Pricing\Render;

class TaxAdjustmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Pricing\Render\TaxAdjustment
     */
    protected $model;

    /**
     * Weee helper mock
     *
     * @var \Magento\Weee\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeHelperMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks and model
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->weeeHelperMock = $this->getMock(
            'Magento\Weee\Helper\Data',
            ['typeOfDisplay', 'isTaxable'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Weee\Pricing\Render\TaxAdjustment',
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

        $weeeCode = \Magento\Weee\Pricing\Adjustment::ADJUSTMENT_CODE;
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
