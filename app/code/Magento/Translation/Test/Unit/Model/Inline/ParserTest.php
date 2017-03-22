<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

/**
 * Class ParserTest to test \Magento\Translation\Model\Inline\Parser
 */
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
     * @var \Zend_Filter_Interface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputFilterMock;

    /**
     * @var StringUtilsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceFactoryMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

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
        $this->translateInlineMock =
            $this->getMockForAbstractClass(\Magento\Framework\Translate\InlineInterface::class);
        $this->appCacheMock = $this->getMockForAbstractClass(\Magento\Framework\App\Cache\TypeListInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->getMockForAbstractClass(\Magento\Store\Api\Data\StoreInterface::class);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->resourceFactoryMock = $this->getMockBuilder(
            \Magento\Translation\Model\ResourceModel\StringUtilsFactory::class
        )
            ->setMethods(['create'])
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Translation\Model\ResourceModel\StringUtils::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->inputFilterMock = $this->getMockBuilder('Zend_Filter_Interface');

        $this->resourceFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resourceMock);
        $this->cacheManagerMock = $this->getMockBuilder(\Magento\Translation\Model\Inline\CacheManager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
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
        $this->translateInlineMock->expects($this->any())->method('getAdditionalHtmlAttribute')->willReturn(null);

        $this->model = $this->objectManager->getObject(
            Parser::class,
            [
                'cacheManager' => $this->cacheManagerMock,
                'resource' => $this->resourceFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'translateInline' => $this->translateInlineMock,
                '_resourceFactory' => $this->resourceMock,
                '_inputFilter' => $this->inputFilterMock,
                '_appState' => $this->appStateMock,
                '_appCache' => $this->appCacheMock,
                '_translateInline' => $this->translateInlineMock
            ]
        );

        $processedContent = $this->model->processResponseBodyString($testContent);
        foreach ($processedAttributes as $attribute) {
            $this->assertContains($attribute, $processedContent, "data-translate attribute not processed correctly");
        }
    }
}
