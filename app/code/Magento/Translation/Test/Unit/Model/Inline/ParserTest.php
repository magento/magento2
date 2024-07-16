<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Inline;

use Laminas\Filter\FilterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\State;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Translation\Model\Inline\CacheManager;
use Magento\Translation\Model\Inline\Parser;
use Magento\Translation\Model\ResourceModel\StringUtils;
use Magento\Translation\Model\ResourceModel\StringUtilsFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ParserTest extends TestCase
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
     * @var InlineInterface|MockObject
     */
    private $translateInlineMock;

    /**
     * @var TypeListInterface|MockObject
     */
    private $appCacheMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var FilterInterface|MockObject
     */
    private $inputFilterMock;

    /**
     * @var StringUtilsFactory|MockObject
     */
    private $resourceFactoryMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var StringUtils|MockObject
     */
    private $resourceMock;

    /**
     * @var CacheManager|MockObject
     */
    private $cacheManagerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $objects = [
            [
                \Magento\Framework\Translate\InlineInterface::class,
                $this->createMock(\Magento\Framework\Translate\InlineInterface::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->translateInlineMock =
            $this->getMockForAbstractClass(InlineInterface::class);
        $this->appCacheMock = $this->getMockForAbstractClass(TypeListInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $this->storeManagerMock->method('getStore')
            ->willReturn($this->storeMock);
        $this->resourceFactoryMock = $this->getMockBuilder(
            StringUtilsFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(StringUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inputFilterMock = $this->getMockForAbstractClass(FilterInterface::class);

        $this->resourceFactoryMock->method('create')
            ->willReturn($this->resourceMock);
        $this->cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            Parser::class,
            [
                'resource' => $this->resourceFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'inputFilter' => $this->inputFilterMock,
                'appState' => $this->appStateMock,
                'appCache' => $this->appCacheMock,
                'translateInline' => $this->translateInlineMock,
                'cacheManager' => $this->cacheManagerMock,
                'escaper' => $this->getMockEscaper()
            ]
        );
    }

    public function testProcessAjaxPostNotAllowed()
    {
        $expected = ['inline' => 'not allowed'];
        $this->translateInlineMock->expects($this->once())
            ->method('isAllowed')
            ->willReturn(false);
        $this->assertEquals($expected, $this->model->processAjaxPost([]));
    }

    public function testProcessAjaxPost()
    {
        $this->translateInlineMock->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);
        $this->model->processAjaxPost([]);
    }

    /**
     * @return void
     */
    public function testProcessResponseBodyString(): void
    {
        $html = file_get_contents(__DIR__ . '/_files/input.html');
        $expectedOutput = file_get_contents(__DIR__ . '/_files/output.html');
        $actualOutput = $this->model->processResponseBodyString($html);
        $this->assertEquals($expectedOutput, $actualOutput);
    }

    /**
     * @return Escaper
     */
    private function getMockEscaper(): Escaper
    {
        $escaper = new Escaper();
        $reflection = new \ReflectionClass($escaper);
        $reflectionProperty = $reflection->getProperty('escaper');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($escaper, new \Magento\Framework\ZendEscaper());
        return $escaper;
    }
}
