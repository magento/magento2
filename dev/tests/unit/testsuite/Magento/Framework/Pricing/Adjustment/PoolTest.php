<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Pricing\Adjustment;

class PoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Adjustment\Pool
     */
    public $model;

    public function setUp()
    {
        $adjustmentsData = [
            'adj1' => ['className' => 'adj1_class', 'sortOrder' => 10],
            'adj2' => ['className' => 'adj2_class', 'sortOrder' => 20],
            'adj3' => ['className' => 'adj3_class', 'sortOrder' => 5],
            'adj4' => ['className' => 'adj4_class', 'sortOrder' => null],
            'adj5' => ['className' => 'adj5_class'],
        ];

        /** @var Factory|\PHPUnit_Framework_MockObject_MockObject $adjustmentFactory */
        $adjustmentFactory = $this->getMockBuilder('Magento\Framework\Pricing\Adjustment\Factory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $adjustmentFactory->expects($this->any())->method('create')->will(
            $this->returnCallback(
                function ($className, $data) {
                    return $className . '|' . $data['sortOrder'];
                }
            )
        );

        $this->model = new Pool($adjustmentFactory, $adjustmentsData);
    }

    public function testGetAdjustments()
    {
        $expectedResult = [
            'adj1' => 'adj1_class|10',
            'adj2' => 'adj2_class|20',
            'adj3' => 'adj3_class|5',
            'adj4' => 'adj4_class|' . \Magento\Framework\Pricing\Adjustment\Pool::DEFAULT_SORT_ORDER,
            'adj5' => 'adj5_class|' . \Magento\Framework\Pricing\Adjustment\Pool::DEFAULT_SORT_ORDER,
        ];

        $result = $this->model->getAdjustments();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getAdjustmentByCodeDataProvider
     */
    public function testGetAdjustmentByCode($code, $expectedResult)
    {
        $result = $this->model->getAdjustmentByCode($code);

        $this->assertEquals($expectedResult, $result);
    }

    public function getAdjustmentByCodeDataProvider()
    {
        return [
            ['adj1', 'adj1_class|10'],
            ['adj2', 'adj2_class|20'],
            ['adj3', 'adj3_class|5'],
            ['adj4', 'adj4_class|' . \Magento\Framework\Pricing\Adjustment\Pool::DEFAULT_SORT_ORDER],
            ['adj5', 'adj5_class|' . \Magento\Framework\Pricing\Adjustment\Pool::DEFAULT_SORT_ORDER],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetAdjustmentByNotExistingCode()
    {
        $this->model->getAdjustmentByCode('not_existing_code');
    }
}
