<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for Magento\Email\Model\BackendTemplate.
 */
namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\BackendTemplate;
use Magento\Framework\ObjectManagerInterface;

class BackendTemplateTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn(['test' => 1]);

        $this->structureMock = $this->createMock(\Magento\Config\Model\Config\Structure::class);
        $this->structureMock->expects($this->any())->method('getFieldPathsByAttribute')->willReturn(['path' => 'test']);

        $this->resourceModelMock = $this->createMock(\Magento\Email\Model\ResourceModel\Template::class);
        $this->resourceModelMock->expects($this->any())
            ->method('getSystemConfigByPathsAndTemplateId')
            ->willReturn(['test_config' => 2015]);
        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManagerMock*/
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Email\Model\ResourceModel\Template::class)
            ->will($this->returnValue($this->resourceModelMock));

        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)->getMock();

        $this->model = $helper->getObject(
            \Magento\Email\Model\BackendTemplate::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'structure' => $this->structureMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManagerMock*/
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
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
