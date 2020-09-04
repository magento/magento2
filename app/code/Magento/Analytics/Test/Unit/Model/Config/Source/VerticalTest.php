<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Config\Source;

use Magento\Analytics\Model\Config\Source\Vertical;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * A unit test for testing of the source model for verticals configuration.
 */
class VerticalTest extends TestCase
{
    /**
     * @var Vertical
     */
    private $subject;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper =
            new ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            Vertical::class,
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
