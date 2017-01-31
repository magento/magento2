<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Inline;

/**
 * Class ParserTest to test \Magento\Translation\Model\Inline\Parser
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Translation\Model\Inline\Parser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Translation\Model\ResourceModel\StringUtilsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Zend_Filter_Interface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputFilterMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appCacheMock;

    /**
     * @var \Magento\Framework\Translate\InlineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translateInlineMock;

    protected function setUp()
    {
        $this->resourceMock = $this->getMockBuilder('Magento\Translation\Model\ResourceModel\StringUtilsFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->inputFilterMock = $this->getMockBuilder('Zend_Filter_Interface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->appCacheMock = $this->getMockBuilder('Magento\Framework\App\Cache\TypeListInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->translateInlineMock= $this->getMockBuilder('Magento\Framework\Translate\InlineInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Translation\Model\Inline\Parser',
            [
                "_resourceFactory" => $this->resourceMock,
                "_storeManager" => $this->storeManagerMock,
                "_inputFilter" => $this->inputFilterMock,
                "_appState" => $this->appStateMock,
                "_appCache" => $this->appCacheMock,
                "_translateInline" => $this->translateInlineMock
            ]
        );
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

        $processedContent = $this->model->processResponseBodyString($testContent);
        foreach ($processedAttributes as $attribute) {
            $this->assertContains($attribute, $processedContent, "data-translate attribute not processed correctly");
        }
    }
}
