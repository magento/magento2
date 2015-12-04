<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Model\Design\Config\Storage */
    protected $model;

    /** @var \Magento\Framework\DB\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionFactoryMock;

    /** @var \Magento\Theme\Model\Design\BackendModelFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModelFactoryMock;

    /** @var \Magento\Theme\Model\Design\Config\ValueChecker|\PHPUnit_Framework_MockObject_MockObject */
    protected $valueCheckerMock;

    /** @var \Magento\Framework\DB\Transaction|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionMock;

    /** @var \Magento\Framework\App\Config\Value|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModelMock;

    /** @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigMock;

    /** @var \Magento\Theme\Api\Data\DesignConfigExtensionInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensionAttributes;

    /** @var \Magento\Theme\Api\Data\DesignConfigDataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigData;

    protected function setUp()
    {
        $this->transactionFactoryMock = $this->getMockBuilder('Magento\Framework\DB\TransactionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->transactionMock = $this->getMockBuilder('Magento\Framework\DB\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModelFactoryMock = $this->getMockBuilder('Magento\Theme\Model\Design\BackendModelFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModelMock = $this->getMockBuilder('Magento\Framework\App\Config\Value')
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueCheckerMock = $this->getMockBuilder('Magento\Theme\Model\Design\Config\ValueChecker')
            ->disableOriginalConstructor()
            ->getMock();
        $this->designConfigMock = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributes = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigExtensionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getDesignConfigData', 'setDesignConfigData'])
            ->getMock();
        $this->designConfigData = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigDataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->transactionMock);

        $this->model = new \Magento\Theme\Model\Design\Config\Storage(
            $this->transactionFactoryMock,
            $this->backendModelFactoryMock,
            $this->valueCheckerMock
        );
    }

    public function testSave()
    {
        $scope = 'website';
        $scopeId = 1;
        $this->designConfigMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$this->designConfigData]);
        $this->designConfigData->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn('value');
        $this->designConfigMock->expects($this->exactly(2))
            ->method('getScope')
            ->willReturn($scope);
        $this->designConfigMock->expects($this->exactly(2))
            ->method('getScopeId')
            ->willReturn($scopeId);
        $this->designConfigData->expects($this->exactly(2))
            ->method('getFieldConfig')
            ->willReturn(['path' => 'design/head/default_title']);
        $this->backendModelFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with([
                'value' => 'value',
                'scope' => $scope,
                'scopeId' => $scopeId,
                'config' => ['path' => 'design/head/default_title']
            ])
            ->willReturn($this->backendModelMock);
        $this->valueCheckerMock->expects($this->once())
            ->method('isDifferentFromDefault')
            ->with('value', $scope, $scopeId, 'design/head/default_title')
            ->willReturn(true);
        $this->transactionMock->expects($this->once())
            ->method('addObject')
            ->with($this->backendModelMock);
        $this->transactionMock->expects($this->once())
            ->method('save');
        $this->transactionMock->expects($this->once())
            ->method('delete');
        $this->model->save($this->designConfigMock);
    }
}
