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
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\Resource;
use Magento\Framework\App\Resource\Config;
use Magento\Framework\Search\Request\Query\Bool;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Dimensions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dimensionsBuilder;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scoreBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scoreBuilderFactory;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match|\PHPUnit_Framework_MockObject_MockObject
     */
    private $matchQueryBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Filter\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Search\Request\FilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $connectionAdapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['select'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connectionAdapter->expects($this->any())->method('select')->will($this->returnValue($this->select));

        $this->resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->any())->method('getConnection')
            ->with(Resource::DEFAULT_READ_RESOURCE)
            ->will($this->returnValue($connectionAdapter));

        $this->scoreBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\ScoreBuilder')
            ->setMethods(['clear'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scoreBuilderFactory = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scoreBuilderFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->scoreBuilder));

        $this->dimensionsBuilder = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\Dimensions')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->setMethods(['getQuery', 'getDimensions'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->matchQueryBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filter = $this->getMockBuilder('Magento\Framework\Search\Request\FilterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filterBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\Filter\Builder')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Mapper',
            [
                'resource' => $this->resource,
                'scoreBuilderFactory' => $this->scoreBuilderFactory,
                'matchQueryBuilder' => $this->matchQueryBuilder,
                'filterBuilder' => $this->filterBuilder,
                'dimensionsBuilder' => $this->dimensionsBuilder,
            ]
        );
    }

    public function testBuildMatchQuery()
    {
        $query = $this->createMatchQuery();

        $this->request->expects($this->once())
            ->method('getDimensions')
            ->will(
                $this->returnValue([$this->createDimension()])
            );
        $this->dimensionsBuilder->expects($this->once())
            ->method('build')
            ->will($this->returnValue('a = b'));

        $this->matchQueryBuilder->expects($this->once())->method('build')
            ->with(
                $this->equalTo($this->scoreBuilder),
                $this->equalTo($this->select),
                $this->equalTo($query),
                $this->equalTo(Bool::QUERY_CONDITION_MUST)
            )
            ->will($this->returnValue($this->select));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->select->expects($this->once())->method('from')->will($this->returnValue($this->select));
        $this->select->expects($this->once())->method('columns')->will($this->returnValue($this->select));

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
    }

    public function testBuildFilterQuery()
    {
        $this->request->expects($this->once())
            ->method('getDimensions')
            ->will(
                $this->returnValue([$this->createDimension()])
            );
        $this->dimensionsBuilder->expects($this->once())
            ->method('build')
            ->will($this->returnValue('a = b'));

        $query = $this->createFilterQuery();
        $query->expects($this->once())->method('getReferenceType')->will($this->returnValue(Filter::REFERENCE_FILTER));
        $query->expects($this->once())->method('getReference')->will($this->returnValue($this->filter));

        $this->select->expects($this->once())->method('from')->will($this->returnValue($this->select));
        $this->select->expects($this->once())->method('columns')->will($this->returnValue($this->select));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->filterBuilder->expects($this->once())->method('build')->will($this->returnValue('(1)'));

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
    }

    public function testBuildBoolQuery()
    {
        $query = $this->createBoolQuery();
        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->matchQueryBuilder->expects($this->exactly(4))->method('build')
            ->will($this->returnValue($this->select));

        $matchQuery = $this->createMatchQuery();
        $filterMatchQuery = $this->createFilterQuery();
        $filterMatchQuery->expects($this->once())->method('getReferenceType')
            ->will($this->returnValue(Filter::REFERENCE_QUERY));
        $filterMatchQuery->expects($this->once())->method('getReference')->will($this->returnValue($matchQuery));

        $filterQuery = $this->createFilterQuery();
        $filterQuery->expects($this->once())->method('getReferenceType')
            ->will($this->returnValue(Filter::REFERENCE_FILTER));
        $filterQuery->expects($this->once())->method('getReference')->will($this->returnValue($this->filter));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->filterBuilder->expects($this->once())->method('build')->will($this->returnValue('(1)'));

        $this->select->expects($this->once())->method('from')->will($this->returnValue($this->select));
        $this->select->expects($this->once())->method('columns')->will($this->returnValue($this->select));

        $query->expects($this->once())
            ->method('getMust')
            ->will(
                $this->returnValue(
                    [
                        $this->createMatchQuery(),
                        $this->createFilterQuery(),
                    ]
                )
            );

        $query->expects($this->once())
            ->method('getShould')
            ->will(
                $this->returnValue(
                    [
                        $this->createMatchQuery(),
                        $filterMatchQuery,
                    ]
                )
            );

        $query->expects($this->once())
            ->method('getMustNot')
            ->will(
                $this->returnValue(
                    [
                        $this->createMatchQuery(),
                        $filterQuery,
                    ]
                )
            );

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown query type 'unknownQuery'
     */
    public function testGetUnknownQueryType()
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\QueryInterface')
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(2))
            ->method('getType')
            ->will($this->returnValue('unknownQuery'));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->mapper->buildQuery($this->request);
    }

    private function createMatchQuery()
    {
        $this->request->expects($this->once())
            ->method('getDimensions')
            ->will(
                $this->returnValue([$this->createDimension()])
            );
        $this->dimensionsBuilder->expects($this->once())
            ->method('build')
            ->will($this->returnValue('a = b'));

        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Match')
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_MATCH));
        return $query;
    }

    /**
     * @return \Magento\Framework\Search\Request\Dimension|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createDimension()
    {
        return $this->getMockBuilder('\Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createFilterQuery()
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Filter')
            ->setMethods(['getType', 'getReferenceType', 'getReference'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(1))
            ->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_FILTER));
        return $query;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createBoolQuery()
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Bool')
            ->setMethods(['getMust', 'getShould', 'getMustNot', 'getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(1))
            ->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_BOOL));
        return $query;
    }
}
