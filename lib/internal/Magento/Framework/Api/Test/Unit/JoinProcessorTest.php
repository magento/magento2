<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit;

use Magento\Framework\Api\Config\Converter;
use Magento\Framework\Api\Config\Reader;

class JoinProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\JoinProcessor
     */
    private $joinProcessor;

    /**
     * @var Reader
     */
    private $configReader;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->configReader = $this->getMockBuilder('Magento\Framework\Api\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->joinProcessor = new \Magento\Framework\Api\JoinProcessor($this->configReader);
    }

    /**
     * Test the processing of the join config for a particular type
     *
     * @dataProvider processDataProvider
     */
    public function testProcess($typeName, $expectedResults)
    {
        $this->configReader->expects($this->once())
            ->method('read')
            ->will($this->returnValue($this->getConfig()));

        $collection = $this->getMockBuilder('Magento\Framework\Data\Collection\Db')
            ->setMethods(['joinField'])
            ->disableOriginalConstructor()
            ->getMock();

        $c = 0;
        foreach ($expectedResults as $result) {
            $collection->expects($this->at($c))
                ->method('joinField')
                ->with($result);
            $c++;
        }

        $this->joinProcessor->process($collection, $typeName);
    }

    public function processDataProvider()
    {
        return [
            'product extension' => [
                'Magento\Catalog\Api\Data\ProductInterface',
                [
                    [
                        'alias' => 'extension_attribute_review_id',
                        'table' => 'reviews',
                        'field' => 'review_id',
                        'join_field' => 'product_id',
                    ],
                ],
            ],
            'customer extension' => [
                'Magento\Customer\Api\Data\CustomerInterface',
                [
                    [
                        'alias' => 'extension_attribute_library_card_id',
                        'table' => 'library_account',
                        'field' => 'library_card_id',
                        'join_field' => 'customer_id',
                    ],
                    [
                        'alias' => 'extension_attribute_reviews',
                        'table' => 'reviews',
                        'field' => 'comment',
                        'join_field' => 'customer_id',
                    ],
                    [
                        'alias' => 'extension_attribute_reviews',
                        'table' => 'reviews',
                        'field' => 'rating',
                        'join_field' => 'customer_id',
                    ],
                ],
            ],
        ];
    }

    private function getConfig() {
        return [
            'Magento\Catalog\Api\Data\ProductInterface' => [
                'review_id' => [
                    Converter::DATA_TYPE => 'string',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "reviews",
                        Converter::JOIN_SELECT_FIELDS => "review_id",
                        Converter::JOIN_JOIN_ON_FIELD => "product_id",
                    ],
                ],
            ],
            'Magento\Customer\Api\Data\CustomerInterface' => [
                'library_card_id' => [
                    Converter::DATA_TYPE => 'string',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "library_account",
                        Converter::JOIN_SELECT_FIELDS => "library_card_id",
                        Converter::JOIN_JOIN_ON_FIELD => "customer_id",
                    ],
                ],
                'reviews' => [
                    Converter::DATA_TYPE => 'Magento\Reviews\Api\Data\Reviews[]',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "reviews",
                        Converter::JOIN_SELECT_FIELDS => "comment,rating",
                        Converter::JOIN_JOIN_ON_FIELD => "customer_id",
                    ],
                ],
            ],
        ];
    }
}
