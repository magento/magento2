<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Variable\Test\Unit\Model\Variable;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\Repository;
use Magento\Variable\Model\ResourceModel\Variable\Collection;
use Magento\Variable\Model\ResourceModel\Variable\CollectionFactory;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\Variable\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $model;

    /**
     * @var Repository|MockObject
     */
    private $assetRepoMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var string
     */
    private $actionUrl = 'action-url';

    /**
     * @var string
     */
    private $jsPluginSourceUrl = 'js-plugin-source';

    /**
     * @var Variables|MockObject
     */
    private $storeVariablesMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $customVarsCollectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private $customVarsCollectionMock;

    /**
     * Set up before tests
     */
    protected function setUp(): void
    {
        $this->assetRepoMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assetRepoMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->jsPluginSourceUrl);
        $this->urlMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->actionUrl);

        $this->storeVariablesMock = $this->getMockBuilder(Variables::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();

        $this->customVarsCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->customVarsCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();

        $this->customVarsCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customVarsCollectionMock);

        // Set up SUT
        $args = [
            'assetRepo' => $this->assetRepoMock,
            'url' => $this->urlMock,
            'collectionFactory' => $this->customVarsCollectionFactoryMock,
            'storesVariables' => $this->storeVariablesMock,
        ];
        $this->model = (new ObjectManager($this))->getObject(Config::class, $args);
    }

    /**
     * Test method getWysiwygPluginSettings
     */
    public function testGetWysiwygPluginSettings()
    {
        $this->storeVariablesMock->expects($this->any())
            ->method('getData')
            ->willReturn([]);
        $this->customVarsCollectionMock->expects($this->any())
            ->method('getData')
            ->willReturn([]);

        $customKey = 'key';
        $customVal = 'val';
        $configObject = new DataObject();
        $configObject->setPlugins([[$customKey => $customVal]]);
        $variablePluginConfig = $this->model->getWysiwygPluginSettings($configObject)['plugins'];
        $customPluginConfig = $variablePluginConfig[0];
        $addedPluginConfig = $variablePluginConfig[1];
        // Verify custom plugin config is present
        $this->assertSame($customVal, $customPluginConfig[$customKey]);
        // Verify added plugin config is present
        $this->assertStringContainsString($this->actionUrl, $addedPluginConfig['options']['onclick']['subject']);
        $this->assertStringContainsString($this->actionUrl, $addedPluginConfig['options']['url']);
        $this->assertStringContainsString($this->jsPluginSourceUrl, $addedPluginConfig['src']);
    }
}
