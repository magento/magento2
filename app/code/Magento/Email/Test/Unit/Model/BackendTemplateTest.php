<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for Magento\Email\Model\BackendTemplate.
 */
namespace Magento\Email\Test\Unit\Model;

use Magento\Config\Model\Config\Structure;
use Magento\Email\Model\BackendTemplate;
use Magento\Email\Model\ResourceModel\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for  adminhtml email template model.
 */
class BackendTemplateTest extends TestCase
{
    /**
     * Backend template mock
     *
     * @var BackendTemplate
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Structure|MockObject
     */
    protected $structureMock;

    /**
     * @var Template|MockObject
     */
    protected $resourceModelMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerBackup;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var Database|MockObject
     */
    private $databaseHelperMock;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn(['test' => 1]);

        $this->structureMock = $this->createMock(Structure::class);
        $this->structureMock->expects($this->any())->method('getFieldPathsByAttribute')->willReturn(['path' => 'test']);

        $this->databaseHelperMock = $this->createMock(Database::class);
        $this->resourceModelMock = $this->createMock(Template::class);
        $this->resourceModelMock->expects($this->any())
            ->method('getSystemConfigByPathsAndTemplateId')
            ->willReturn(['test_config' => 2015]);
        /** @var ObjectManagerInterface|MockObject $objectManagerMock*/
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function ($value) {
                    switch ($value) {
                        case Database::class:
                            return ($this->databaseHelperMock);
                        case Template::class:
                            return ($this->resourceModelMock);
                        default:
                            return(null);
                    }
                }
            );

        ObjectManager::setInstance($objectManagerMock);

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();

        $this->model = $helper->getObject(
            BackendTemplate::class,
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
        /** @var ObjectManagerInterface|MockObject $objectManagerMock*/
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        ObjectManager::setInstance($objectManagerMock);
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
