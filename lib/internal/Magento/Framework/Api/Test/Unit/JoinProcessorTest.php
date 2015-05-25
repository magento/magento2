<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit;

use Magento\Framework\Api\Config\Converter;
use Magento\Framework\Api\Config\Reader;
use Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinData;
use Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinDataFactory;

class JoinProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\JoinProcessor
     */
    private $joinProcessor;

    /**
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReader;

    /**
     * @var ExtensionAttributeJoinDataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributeJoinDataFactory;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->configReader = $this->getMockBuilder('Magento\Framework\Api\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributeJoinDataFactory = $this
            ->getMockBuilder('Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinDataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->joinProcessor = new \Magento\Framework\Api\JoinProcessor(
            $this->configReader,
            $this->extensionAttributeJoinDataFactory
        );
    }

    /**
     * Test the processing of the join config for a particular type
     */
    public function testProcess()
    {
        $this->configReader->expects($this->once())
            ->method('read')
            ->will($this->returnValue($this->getConfig()));

        $collection = $this->getMockBuilder('Magento\Framework\Data\Collection\Db')
            ->setMethods(['joinExtensionAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $extensionAttributeJoinData = new ExtensionAttributeJoinData();
        $this->extensionAttributeJoinDataFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributeJoinData);

        $collection->expects($this->once())->method('joinExtensionAttribute')->with($extensionAttributeJoinData);

        $this->joinProcessor->process($collection, 'Magento\Catalog\Api\Data\ProductInterface');
        $this->assertEquals('reviews', $extensionAttributeJoinData->getReferenceTable());
        $this->assertEquals('extension_attribute_review_id', $extensionAttributeJoinData->getReferenceTableAlias());
        $this->assertEquals('product_id', $extensionAttributeJoinData->getReferenceField());
        $this->assertEquals('id', $extensionAttributeJoinData->getJoinField());
        $this->assertEquals('review_id', $extensionAttributeJoinData->getSelectField());
    }

    private function getConfig() {
        return [
            'Magento\Catalog\Api\Data\ProductInterface' => [
                'review_id' => [
                    Converter::DATA_TYPE => 'string',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "reviews",
                        Converter::JOIN_REFERENCE_FIELD => "product_id",
                        Converter::JOIN_SELECT_FIELDS => "review_id",
                        Converter::JOIN_JOIN_ON_FIELD => "id",
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
