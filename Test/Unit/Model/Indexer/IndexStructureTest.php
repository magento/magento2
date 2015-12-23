<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Indexer\IndexStructure;

class IndexStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexStructure
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Elasticsearch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;


    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->adapter = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Elasticsearch')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            'Magento\Elasticsearch\Model\Indexer\IndexStructure',
            [
                'adapter' => $this->adapter,
            ]
        );
    }

    public function testDelete()
    {
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter->expects($this->any())
            ->method('cleanIndex');

        $this->model->delete('product', [$dimension]);
    }

    public function testCreate()
    {
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter->expects($this->any())
            ->method('checkIndex');

        $this->model->create('product', [], [$dimension]);
    }
}
