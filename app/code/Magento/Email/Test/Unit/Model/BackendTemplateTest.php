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

/**
 * Tests for  adminhtml email template model.
 */
class BackendTemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Backend template mock
     *
     * @var BackendTemplate
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Config\Model\Config\Structure|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $structureMock;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceModelMock;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManagerBackup;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit\Framework\MockObject\MockObject
     */
    private $databaseHelperMock;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn(['test' => 1]);

        $this->structureMock = $this->createMock(\Magento\Config\Model\Config\Structure::class);
        $this->structureMock->expects($this->any())->method('getFieldPathsByAttribute')->willReturn(['path' => 'test']);

        $this->databaseHelperMock = $this->createMock(\Magento\MediaStorage\Helper\File\Storage\Database::class);
        $this->resourceModelMock = $this->createMock(\Magento\Email\Model\ResourceModel\Template::class);
        $this->resourceModelMock->expects($this->any())
            ->method('getSystemConfigByPathsAndTemplateId')
            ->willReturn(['test_config' => 2015]);
        /** @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject $objectManagerMock*/
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function ($value) {
                    switch ($value) {
                        case \Magento\MediaStorage\Helper\File\Storage\Database::class:
                            return ($this->databaseHelperMock);
                        case \Magento\Email\Model\ResourceModel\Template::class:
                            return ($this->resourceModelMock);
                        default:
                            return(null);
                    }
                }
            );

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

    protected function tearDown(): void
    {
        parent::tearDown();
        /** @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject $objectManagerMock*/
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
