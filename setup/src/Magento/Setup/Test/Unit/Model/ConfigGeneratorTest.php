<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;


use Magento\Framework\Config\ConfigOptionsListConstants;

class ConfigGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\App\DeploymentConfig | \PHPUnit_Framework_MockObject_MockObject */
    private $deploymentConfigMock;
    /** @var  \Magento\Setup\Model\ConfigGenerator | \PHPUnit_Framework_MockObject_MockObject */
    private $model;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->deploymentConfigMock = $this->getMockBuilder('Magento\Framework\App\DeploymentConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            'Magento\Setup\Model\ConfigGenerator',
            ['deploymentConfig' => $this->deploymentConfigMock]
        );
    }

    public function testCreateXFrameConfig()
    {
        $this->deploymentConfigMock->expects($this->atLeastOnce())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT)
            ->willReturn(null);
        $configData = $this->model->createXFrameConfig();
        $this->assertSame('SAMEORIGIN', $configData->getData()[ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT]);
    }
}
