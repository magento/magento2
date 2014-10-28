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
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\TestFramework\Helper\ObjectManager;

class TermTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Term
     */
    private $term;

    /**
     * @var Metrics|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metricsBuilder;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var RequestBucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucket;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->metricsBuilder = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics'
        )
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['where', 'columns', 'group'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->term = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Term',
            ['metricsBuilder' => $this->metricsBuilder]
        );
    }

    public function testBuild()
    {
        $productIds = [1, 2, 3];
        $metrics = ['count' => 'count(*)'];

        $this->select->expects($this->once())->method('where')->withConsecutive(
            ['main_table.entity_id IN (?)', $productIds]
        );
        $this->select->expects($this->once())->method('columns')->withConsecutive([$metrics]);
        $this->select->expects($this->once())->method('group')->withConsecutive(['value']);

        $this->metricsBuilder->expects($this->once())->method('build')->willReturn($metrics);

        $result = $this->term->build($this->select, $this->bucket, $productIds);

        $this->assertEquals($this->select, $result);
    }
}
