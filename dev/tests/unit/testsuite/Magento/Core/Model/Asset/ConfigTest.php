<?php
/**
 * Tests Magento\Core\Model\Asset\Config
 *
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

