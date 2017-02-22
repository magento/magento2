<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use \Magento\Catalog\Controller\Adminhtml\Product\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $wysiwygConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);
        $this->registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->wysiwygConfigMock = $this->getMock(
            'Magento\Cms\Model\Wysiwyg\Config',
            ['setStoreId'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $methods = ['setStoreId', 'setData', 'load', '__wakeup', 'setAttributeSetId', 'setTypeId'];
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $methods, [], '', false);

        $this->builder = new Builder(
            $this->productFactoryMock,
            $this->loggerMock,
            $this->registryMock,
            $this->wysiwygConfigMock
        );
    }

    public function testBuildWhenProductExistAndPossibleToLoadProduct()
    {
        $valueMap = [
            ['id', null, 2],
            ['store', 0, 'some_store'],
            ['type', null, 'type_id'],
            ['set', null, 3],
            ['store', null, 'store'],
        ];
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($valueMap));
        $this->productFactoryMock->expects(
            $this->once()
        )
            ->method(
                'create'
            )
            ->will(
                $this->returnValue($this->productMock)
            );
        $this->productMock->expects(
            $this->once()
        )
            ->method(
                'setStoreId'
            )
            ->with(
                'some_store'
            )
            ->will(
                $this->returnSelf()
            );
        $this->productMock->expects($this->never())
            ->method('setTypeId');
        $this->productMock->expects($this->once())
            ->method('load')
            ->with(2)
            ->will($this->returnSelf());
        $this->productMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with(3)
            ->will($this->returnSelf());
        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
        ];
        $this->registryMock->expects($this->any())
            ->method('register')
            ->will($this->returnValueMap($registryValueMap));
        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with('store');
        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }

    public function testBuildWhenImpossibleLoadProduct()
    {
        $valueMap = [
            ['id', null, 15],
            ['store', 0, 'some_store'],
            ['type', null, 'type_id'],
            ['set', null, 3],
            ['store', null, 'store'],
        ];
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($valueMap));
        $this->productFactoryMock->expects(
            $this->once()
        )
            ->method(
                'create'
            )
            ->will(
                $this->returnValue($this->productMock)
            );
        $this->productMock->expects(
            $this->once()
        )
            ->method(
                'setStoreId'
            )
            ->with(
                'some_store'
            )
            ->will(
                $this->returnSelf()
            );
        $this->productMock->expects(
            $this->once()
        )
            ->method(
                'setTypeId'
            )
            ->with(
                \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE
            )
            ->will(
                $this->returnSelf()
            );
        $this->productMock->expects(
            $this->once()
        )
            ->method(
                'load'
            )
            ->with(
                15
            )
            ->will(
                $this->throwException(new \Exception())
            );
        $this->loggerMock->expects($this->once())
            ->method('critical');
        $this->productMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with(3)
            ->will($this->returnSelf());
        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
        ];
        $this->registryMock->expects($this->any())
            ->method('register')
            ->will($this->returnValueMap($registryValueMap));
        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with('store');
        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }

    public function testBuildWhenProductNotExist()
    {
        $valueMap = [
            ['id', null, null],
            ['store', 0, 'some_store'],
            ['type', null, 'type_id'],
            ['set', null, 3],
            ['store', null, 'store'],
        ];
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($valueMap));
        $this->productFactoryMock->expects(
            $this->once()
        )
            ->method(
                'create'
            )
            ->will(
                $this->returnValue($this->productMock)
            );
        $this->productMock->expects(
            $this->once()
        )
            ->method(
                'setStoreId'
            )
            ->with(
                'some_store'
            )
            ->will(
                $this->returnSelf()
            );
        $productValueMap = [
            ['type_id', $this->productMock],
            [\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE, $this->productMock],
        ];
        $this->productMock->expects($this->any())
            ->method('setTypeId')
            ->will($this->returnValueMap($productValueMap));
        $this->productMock->expects($this->never())
            ->method('load');
        $this->productMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with(3)
            ->will($this->returnSelf());
        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
        ];
        $this->registryMock->expects($this->any())
            ->method('register')
            ->will($this->returnValueMap($registryValueMap));
        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with('store');
        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }
}
