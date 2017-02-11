<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Config\Source;

/**
 * A unit test for testing of the source model for verticals configuration.
 */
class VerticalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Analytics\Model\Config\Source\Vertical
     */
    private $subject;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\Model\Config\Source\Vertical::class,
            [
                'verticals' => [
                    'Apps and Games',
                    'Athletic/Sporting Goods',
                    'Art and Design'
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function testToOptionArray()
    {
        $expectedOptionsArray = [
            ['value' => '', 'label' => __('--Please Select--')],
            ['value' => 'Apps and Games', 'label' => __('Apps and Games')],
            ['value' => 'Athletic/Sporting Goods', 'label' => __('Athletic/Sporting Goods')],
            ['value' => 'Art and Design', 'label' => __('Art and Design')]
        ];

        $this->assertEquals(
            $expectedOptionsArray,
            $this->subject->toOptionArray()
        );
    }
}
