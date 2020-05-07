<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config\Storage;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Writer
     */
    protected $model;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $resource;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->resource = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = $this->objectManager->getObject(
            Writer::class,
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
