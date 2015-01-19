<?php
/**
 * Tests Magento\Core\Model\Asset\Config
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Asset;

class ConfigTest extends \Magento\Test\BaseTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Core\Model\Asset\Config
     */
    private $model;

    public function setUp()
    {
        parent::setUp();
        $this->scopeConfigMock = $this->basicMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->model = $this->objectManager->getObject('Magento\Core\Model\Asset\Config',
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @dataProvider booleanDataProvider
     */
    public function testIsMergeCssFiles($booleanData)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_MERGE_CSS_FILES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn($booleanData);
        $this->assertSame($booleanData, $this->model->isMergeCssFiles());
    }

    /**
     * @dataProvider booleanDataProvider
     */
    public function testIsMergeJsFiles($booleanData)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_MERGE_JS_FILES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn($booleanData);
        $this->assertSame($booleanData, $this->model->isMergeJsFiles());
    }

    public function testIsAssetMinification()
    {
        $contentType = 'content type';
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                sprintf(Config::XML_PATH_MINIFICATION_ENABLED, $contentType),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(true);
        $this->assertTrue($this->model->isAssetMinification($contentType));
    }

    public function testGetAssetMinificationAdapter()
    {
        $contentType = 'content type';
        $adapter = 'adapter';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                sprintf(Config::XML_PATH_MINIFICATION_ADAPTER, $contentType),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn($adapter);
        $this->assertSame($adapter, $this->model->getAssetMinificationAdapter($contentType));
    }
}
