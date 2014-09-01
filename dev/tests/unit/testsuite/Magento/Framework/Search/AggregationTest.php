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
namespace Magento\Framework\Search;

use Magento\TestFramework\Helper\ObjectManager;

class AggregationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Aggregation |\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregation;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $buckets = [];
        for ($count = 0; $count < 5; $count++) {
            $bucket = $this->getMockBuilder('Magento\Framework\Search\Bucket')
                ->disableOriginalConstructor()
                ->getMock();

            $bucket->expects($this->any())->method('getName')->will($this->returnValue("$count"));
            $bucket->expects($this->any())->method('getValue')->will($this->returnValue($count));
            $buckets[] = $bucket;
        }

        $this->aggregation = $helper->getObject(
            '\Magento\Framework\Search\Aggregation',
            [
                'buckets' => $buckets,
            ]
        );
    }

    public function testGetIterator()
    {
        $count = 0;
        foreach ($this->aggregation as $bucket) {
             $this->assertEquals($bucket->getName(), "$count");
             $this->assertEquals($bucket->getValue(), $count);
             $count++;
        }
    }

    public function testGetBucketNames()
    {
        $this->assertEquals(
            $this->aggregation->getBucketNames(),
            ['0', '1', '2', '3', '4']
        );
    }

    public function testGetBucket()
    {
        $bucket = $this->aggregation->getBucket('3');
        $this->assertEquals($bucket->getName(), '3');
        $this->assertEquals($bucket->getValue(), 3);
    }
}
