<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config\Storage;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\Storage\Writer
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resource = $this->getMockBuilder('Magento\Framework\App\Config\ConfigResource\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManager->getObject(
            'Magento\Framework\App\Config\Storage\Writer',
            ['resource' => $this->resource]
        );
    }

    public function testDelete()
    {
        $this->resource->expects($this->once())
            ->method('deleteConfig')
            ->with('path', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->model->delete('path');
    }

    public function testDeleteOptions()
    {
        $scope = 'scope';
        $scopeId = '1';
        $this->resource->expects($this->once())
            ->method('deleteConfig')
            ->with('path', $scope, $scopeId);
        $this->model->delete('path', $scope, $scopeId);
    }

    public function testSave()
    {
        $this->resource->expects($this->once())
            ->method('saveConfig')
            ->with('path', 'value', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->model->save('path', 'value');
    }
}
