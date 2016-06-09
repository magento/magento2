<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Inline;

use Magento\Translation\Model\Inline\Parser;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Translation\Model\ResourceModel\StringUtilsFactory;
use Magento\Translation\Model\ResourceModel\StringUtils;
use Magento\Translation\Model\Inline\CacheManager;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parser
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var InlineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translateInlineMock;

    /**
     * @var TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appCacheMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var StringUtilsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceFactoryMock;

    /**
     * @var StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var CacheManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManagerMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->translateInlineMock = $this->getMockForAbstractClass('Magento\Framework\Translate\InlineInterface');
        $this->appCacheMock = $this->getMockForAbstractClass('Magento\Framework\App\Cache\TypeListInterface');
        $this->storeManagerMock = $this->getMockForAbstractClass('Magento\Store\Model\StoreManagerInterface');
        $this->storeMock = $this->getMockForAbstractClass('Magento\Store\Api\Data\StoreInterface');
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->resourceFactoryMock = $this->getMockBuilder('Magento\Translation\Model\ResourceModel\StringUtilsFactory')
            ->setMethods(['create'])
            ->getMock();
        $this->resourceMock = $this->getMockBuilder('Magento\Translation\Model\ResourceModel\StringUtils')
            ->disableOriginalConstructor()
            ->setMethods([])
 	 	    ->getMock();
        $this->resourceFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resourceMock);
        $this->cacheManagerMock = $this->getMockBuilder('Magento\Translation\Model\Inline\CacheManager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    public function testProcessAjaxPostNotAllowed()
    {
        $expected = ['inline' => 'not allowed'];
        $this->translateInlineMock->expects($this->once())
            ->method('isAllowed')
            ->willReturn(false);
        $this->model = $this->objectManager->getObject(
            Parser::class,
            ['translateInline' => $this->translateInlineMock]
        );
        $this->assertEquals($expected, $this->model->processAjaxPost([]));
    }

    public function testProcessAjaxPost()
    {
        $this->translateInlineMock->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);
        $this->model = $this->objectManager->getObject(
            Parser::class,
            [
                'cacheManager' => $this->cacheManagerMock,
                'resource' => $this->resourceFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'translateInline' => $this->translateInlineMock
            ]
        );
        $this->model->processAjaxPost([]);
    }
}
