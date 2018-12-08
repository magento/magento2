<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Unit\Model\Variable;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\Repository;
use Magento\Variable\Model\ResourceModel\Variable\Collection;
use Magento\Variable\Model\ResourceModel\Variable\CollectionFactory;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\Variable\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $model;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepoMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var Variables|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeVariablesMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customVarsCollectionFactoryMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customVarsCollectionMock;

    /**
     * Set up before tests
     */
    protected function setUp()
    {
        $this->assetRepoMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepoMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->jsPluginSourceUrl);
        $this->urlMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->actionUrl);

        $this->storeVariablesMock = $this->getMockBuilder(Variables::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->customVarsCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->customVarsCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
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
        $configObject = new \Magento\Framework\DataObject();
        $configObject->setPlugins([[$customKey => $customVal]]);
        $variablePluginConfig = $this->model->getWysiwygPluginSettings($configObject)['plugins'];
        $customPluginConfig = $variablePluginConfig[0];
        $addedPluginConfig = $variablePluginConfig[1];
        // Verify custom plugin config is present
        $this->assertSame($customVal, $customPluginConfig[$customKey]);
        // Verify added plugin config is present
        $this->assertContains($this->actionUrl, $addedPluginConfig['options']['onclick']['subject']);
        $this->assertContains($this->actionUrl, $addedPluginConfig['options']['url']);
        $this->assertContains($this->jsPluginSourceUrl, $addedPluginConfig['src']);
    }
}
