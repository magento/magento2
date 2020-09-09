<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Inline;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\State;
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
     * @var \Zend_Filter_Interface|MockObject
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
            ->setMethods(['create'])
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(StringUtils::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->inputFilterMock = $this->getMockForAbstractClass('Zend_Filter_Interface');

        $this->resourceFactoryMock->method('create')
            ->willReturn($this->resourceMock);
        $this->cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->appStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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

    public function testProcessResponseBodyStringProcessingAttributesCorrectly()
    {
        $testContent = file_get_contents(__DIR__ . '/_files/datatranslate_fixture.html');
        $processedAttributes = [
            "data-translate=\"[{'shown':'* Required Fields','translated':'* Required Fields',"
            . "'original':'* Required Fields','location':'Tag attribute (ALT, TITLE, etc.)'}]\"",
            "data-translate=\"[{'shown':'Email','translated':'Email','original':'Email',"
            . "'location':'Tag attribute (ALT, TITLE, etc.)'}]\"",
            "data-translate=\"[{'shown':'Password','translated':'Password','original':'Password',"
            . "'location':'Tag attribute (ALT, TITLE, etc.)'}]\""
        ];
        $this->translateInlineMock->method('getAdditionalHtmlAttribute')->willReturn(null);

        $processedContent = $this->model->processResponseBodyString($testContent);
        foreach ($processedAttributes as $attribute) {
            $this->assertStringContainsString(
                $attribute,
                $processedContent,
                'data-translate attribute not processed correctly'
            );
        }
    }
}
