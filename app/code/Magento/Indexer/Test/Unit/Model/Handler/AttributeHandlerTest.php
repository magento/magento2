<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Handler;

use Magento\Customer\Model\Resource\Customer\Collection;

class AttributeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Indexer\Model\Handler\AttributeHandler */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Indexer\Model\Handler\AttributeHandler();
    }

    public function testPrepareSql()
    {
        $fieldInfo = [
            'origin' => 'field',
        ];
        $alias = 'alias';


        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $sourceMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with($fieldInfo['origin'], 'left')
            ->willReturnSelf();

        $this->model->prepareSql($sourceMock, $alias, $fieldInfo);
    }

    public function testPrepareSqlWithBind()
    {
        $fieldInfo = [
            'name' => 'name',
            'origin' => 'origin',
            'entity' => 'entity',
            'bind' => 'bind',
        ];
        $alias = 'alias';

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $sourceMock->expects($this->once())
            ->method('joinAttribute')
            ->with(
                $fieldInfo['name'],
                $fieldInfo['entity'] . '/' . $fieldInfo['origin'],
                $fieldInfo['bind'],
                null,
                'left',
                null
            )->willReturnSelf();

        $this->model->prepareSql($sourceMock, $alias, $fieldInfo);
    }
}
