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

class QueryResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Document[]
     */
    private $documents = [];

    /**
     * @var \Magento\Framework\Search\Aggregation
     */
    private $aggregations = [];

    /**
     * @var \Magento\Framework\Search\QueryResponse | \PHPUnit_Framework_MockObject_MockObject
     */
    private $queryResponse;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        for ($count = 0; $count < 5; $count++) {
            $document = $this->getMockBuilder('Magento\Framework\Search\Document')
                ->disableOriginalConstructor()
                ->getMock();

            $document->expects($this->any())->method('getId')->will($this->returnValue($count));
            $this->documents[] = $document;
        }

        $this->aggregations = $this->getMockBuilder('Magento\Framework\Search\Aggregation')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryResponse = $helper->getObject(
            'Magento\Framework\Search\QueryResponse',
            [
                'documents' => $this->documents,
                'aggregations' => $this->aggregations,
            ]
        );
    }

    public function testGetIterator()
    {
        $count = 0;
        foreach ($this->queryResponse as $document) {
            $this->assertEquals($document->getId(), $count);
            $count++;
        }
    }

    public function testCount()
    {
        $this->assertEquals(count($this->queryResponse), 5);
    }

    public function testGetAggregations()
    {
        $aggregations = $this->queryResponse->getAggregations();
        $this->assertInstanceOf('Magento\Framework\Search\Aggregation', $aggregations);
    }
}
