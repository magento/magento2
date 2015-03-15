<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\Email\Model\Resource\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModelMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn(['test' => 1]);

        $this->structureMock = $this->getMock('Magento\Config\Model\Config\Structure', [], [], '', false);
        $this->structureMock->expects($this->any())->method('getFieldPathsByAttribute')->willReturn(['path' => 'test']);

        $this->resourceModelMock = $this->getMock('Magento\Email\Model\Resource\Template', [], [], '', false);
        $this->resourceModelMock->expects($this->any())->method('getSystemConfigByPathsAndTemplateId')->willReturn(['test_config' => 2015]);
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->with('Magento\Email\Model\Resource\Template')
            ->will($this->returnValue($this->resourceModelMock));
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $this->model = $helper->getObject(
            'Magento\Email\Model\BackendTemplate',
            ['scopeConfig' => $this->scopeConfigMock, 'structure' => $this->structureMock]
        );
    }

    public function testGetSystemConfigPathsWhereUsedAsDefaultNoTemplateCode()
    {
        $this->assertEquals([], $this->model->getSystemConfigPathsWhereUsedAsDefault());
    }

    public function testGetSystemConfigPathsWhereUsedAsDefaultValidTemplateCode()
    {
        $this->model->setData('orig_template_code', 1);
        $this->assertEquals([['path' => 'test']], $this->model->getSystemConfigPathsWhereUsedAsDefault());
    }

    public function testGetSystemConfigPathsWhereUsedCurrentlyNoId()
    {
        $this->assertEquals([], $this->model->getSystemConfigPathsWhereUsedCurrently());
    }

    public function testGetSystemConfigPathsWhereUsedCurrentlyValidId()
    {
        $this->model->setId(1);
        $this->assertEquals(['test_config' => 2015], $this->model->getSystemConfigPathsWhereUsedCurrently());
    }
}
