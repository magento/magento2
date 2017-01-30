<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Unit\Model\Variable;


use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetWysiwygPluginSettings()
    {
        $jsPluginSourceUrl = 'js-plugin-source';
        $actionUrl = 'action-url';
        $assetRepoMock = $this->getMockBuilder('Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $urlMock = $this->getMockBuilder('Magento\Backend\Model\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $assetRepoMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($jsPluginSourceUrl);
        $urlMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($actionUrl);

        // Set up SUT
        $args = [
            'assetRepo' => $assetRepoMock,
            'url' => $urlMock
        ];
        $model = (new ObjectManager($this))->getObject('Magento\Variable\Model\Variable\Config', $args);

        $customKey = 'key';
        $customVal = 'val';
        $configObject = new \Magento\Framework\DataObject();
        $configObject->setPlugins([[$customKey => $customVal]]);

        $variablePluginConfig = $model->getWysiwygPluginSettings($configObject)['plugins'];
        $customPluginConfig = $variablePluginConfig[0];
        $addedPluginConfig = $variablePluginConfig[1];

        // Verify custom plugin config is present
        $this->assertSame($customVal, $customPluginConfig[$customKey]);

        // Verify added plugin config is present
        $this->assertContains($actionUrl, $addedPluginConfig['options']['onclick']['subject']);
        $this->assertContains($actionUrl, $addedPluginConfig['options']['url']);
        $this->assertContains($jsPluginSourceUrl, $addedPluginConfig['src']);
    }
}
