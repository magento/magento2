<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search\Response;

use Magento\TestFramework\Helper\ObjectManager;

class AggregationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Response\Aggregation |\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregation;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $buckets = [];
        $bucket = $this->getMockBuilder('Magento\Framework\Search\Response\Bucket')
            ->disableOriginalConstructor()
            ->getMock();

        $bucket->expects($this->any())->method('getName')->will($this->returnValue('1'));
        $bucket->expects($this->any())->method('getValues')->will($this->returnValue(1));
        $buckets[1] = $bucket;

        $this->aggregation = $helper->getObject(
            'Magento\Framework\Search\Response\Aggregation',
            [
                'buckets' => $buckets,
            ]
        );
    }

    public function testGetIterator()
    {
        foreach ($this->aggregation as $bucket) {
            $this->assertEquals($bucket->getName(), "1");
            $this->assertEquals($bucket->getValues(), 1);
        }
    }

    public function testGetBucketNames()
    {
        $this->assertEquals(
            $this->aggregation->getBucketNames(),
            ['1']
        );
    }

    public function testGetBucket()
    {
        $bucket = $this->aggregation->getBucket('1');
        $this->assertEquals($bucket->getName(), '1');
        $this->assertEquals($bucket->getValues(), 1);
    }
}
