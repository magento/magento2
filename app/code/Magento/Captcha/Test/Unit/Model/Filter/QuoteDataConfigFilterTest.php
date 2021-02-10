<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model\Filter;

use Magento\Captcha\Model\Filter\QuoteDataConfigFilter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for Class \Magento\Captcha\Model\Filter\QuoteDataConfigFilter
 */
class QuoteDataConfigFilterTest extends TestCase
{

    /**
     * @var QuoteDataConfigFilter
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Initialize Class Dependencies
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->model = $this->objectManager->getObject(
            QuoteDataConfigFilter::class,
            [
                'filterList' => ['test1', 'test2'],
            ]
        );
    }

    /**
     * Test Process method
     *
     * @return void
     */
    public function testProcess(): void
    {
        $config = [
            'quoteData' =>
                [
                    'test1' => 1,
                    'test2' => 2,
                    'test3' => 3
                ]
        ];

        $expected = [
            'quoteData' =>
                [
                    'test3' => 3
                ]
        ];

        $this->assertEquals($expected, $this->model->process($config));
    }
}
