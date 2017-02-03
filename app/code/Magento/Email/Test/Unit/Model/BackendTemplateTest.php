<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for Magento\Email\Model\BackendTemplate.
 */
namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\BackendTemplate;

class BackendTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Backend template mock
     *
     * @var BackendTemplate
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Config\Model\Config\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structureMock;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModelMock;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManagerBackup;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn(['test' => 1]);

        $this->structureMock = $this->getMock('Magento\Config\Model\Config\Structure', [], [], '', false);
        $this->structureMock->expects($this->any())->method('getFieldPathsByAttribute')->willReturn(['path' => 'test']);

        $this->resourceModelMock = $this->getMock('Magento\Email\Model\ResourceModel\Template', [], [], '', false);
        $this->resourceModelMock->expects($this->any())->method('getSystemConfigByPathsAndTemplateId')->willReturn(['test_config' => 2015]);
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->with('Magento\Email\Model\ResourceModel\Template')
            ->will($this->returnValue($this->resourceModelMock));

        try {
            $this->objectManagerBackup = \Magento\Framework\App\ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            $this->objectManagerBackup = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER)
                ->create($_SERVER);
        }
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $this->model = $helper->getObject(
            'Magento\Email\Model\BackendTemplate',
            ['scopeConfig' => $this->scopeConfigMock, 'structure' => $this->structureMock]
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerBackup);
    }

    public function testGetSystemConfigPathsWhereCurrentlyUsedNoId()
    {
        $this->assertEquals([], $this->model->getSystemConfigPathsWhereCurrentlyUsed());
    }

    public function testGetSystemConfigPathsWhereCurrentlyUsedValidId()
    {
        $this->model->setId(1);
        $this->assertEquals(['test_config' => 2015], $this->model->getSystemConfigPathsWhereCurrentlyUsed());
    }
}
