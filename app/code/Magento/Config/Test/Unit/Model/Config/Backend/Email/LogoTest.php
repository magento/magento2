<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend\Email;

use Magento\Config\Model\Config\Backend\Email\Logo;
use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LogoTest extends TestCase
{
    /** @var Logo */
    protected $model;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var Registry|MockObject */
    protected $registryMock;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfigMock;

    /** @var TypeListInterface|MockObject */
    protected $typeListMock;

    /** @var UploaderFactory|MockObject */
    protected $uploaderFactoryMock;

    /** @var RequestDataInterface|MockObject */
    protected $requestDataMock;

    /** @var Filesystem|MockObject */
    protected $filesystemMock;

    /** @var WriteInterface|MockObject */
    protected $writeMock;

    /** @var Uploader|MockObject */
    protected $uploaderMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->typeListMock = $this->getMockBuilder(TypeListInterface::class)
            ->getMockForAbstractClass();
        $this->uploaderFactoryMock = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->requestDataMock
            = $this->getMockBuilder(RequestDataInterface::class)
                ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writeMock = $this->getMockBuilder(WriteInterface::class)
            ->getMock();
        $this->uploaderMock = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->uploaderFactoryMock->method('create')
            ->willReturn($this->uploaderMock);

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->writeMock);

        $this->model = new Logo(
            $this->contextMock,
            $this->registryMock,
            $this->scopeConfigMock,
            $this->typeListMock,
            $this->uploaderFactoryMock,
            $this->requestDataMock,
            $this->filesystemMock
        );
    }

    public function testBeforeSave()
    {
        $value = 'value';
        $groupId = 1;
        $field = 'field';
        $tmpFileName = 'tmp_file_name';
        $path = 'path';
        $scope = 'scope';
        $scopeCode = 'code';
        $oldValue = 'old_value';

        $this->model->setValue($value);
        $this->model->setGroupId($groupId);
        $this->model->setField($field);
        $this->model->setPath($path);
        $this->model->setScope($scope);
        $this->model->setScopeCode($scopeCode);
        $_FILES['groups']['tmp_name'][$groupId]['fields'][$field]['value'] = $tmpFileName;

        $this->scopeConfigMock->method('getValue')
            ->with($path, $scope, $scopeCode)
            ->willReturn($oldValue);

        $this->writeMock->expects($this->once())
            ->method('delete')
            ->with(Logo::UPLOAD_DIR . '/' . $oldValue)
            ->willReturn(true);

        $this->uploaderMock->method('save')
            ->willReturn(['file' => $oldValue]);

        $this->assertEquals($this->model, $this->model->beforeSave());
    }

    public function testBeforeSaveWithTmpInValue()
    {
        $tmpFileName = 'tmp_file_name';
        $value = ['tmp_name' => $tmpFileName, 'name' => 'name'];
        $groupId = 1;
        $field = 'field';
        $path = 'path';
        $scope = 'scope';
        $scopeCode = 'code';
        $oldValue = 'old_value';

        $this->model->setValue($value);
        $this->model->setGroupId($groupId);
        $this->model->setField($field);
        $this->model->setPath($path);
        $this->model->setScope($scope);
        $this->model->setScopeCode($scopeCode);

        $this->scopeConfigMock->method('getValue')
            ->with($path, $scope, $scopeCode)
            ->willReturn($oldValue);

        $this->writeMock->expects($this->once())
            ->method('delete')
            ->with(Logo::UPLOAD_DIR . '/' . $oldValue)
            ->willReturn(true);

        $this->uploaderMock->method('save')
            ->willReturn(['file' => $oldValue]);

        $this->assertEquals($this->model, $this->model->beforeSave());
    }

    public function testBeforeSaveWithDelete()
    {
        $tmpFileName = '';
        $value = ['delete' => 1, 'tmp_name' => $tmpFileName, 'name' => 'name'];
        $groupId = 1;
        $field = 'field';
        $path = 'path';
        $scope = 'scope';
        $scopeCode = 'code';
        $oldValue = 'old_value';

        $this->model->setValue($value);
        $this->model->setGroupId($groupId);
        $this->model->setField($field);
        $this->model->setPath($path);
        $this->model->setScope($scope);
        $this->model->setScopeCode($scopeCode);

        $this->scopeConfigMock->method('getValue')
            ->with($path, $scope, $scopeCode)
            ->willReturn($oldValue);

        $this->writeMock->expects($this->once())
            ->method('delete')
            ->with(Logo::UPLOAD_DIR . '/' . $oldValue)
            ->willReturn(true);

        $this->assertEquals($this->model, $this->model->beforeSave());
    }

    public function testBeforeSaveWithoutOldValue()
    {
        $tmpFileName = '';
        $value = ['delete' => 1, 'tmp_name' => $tmpFileName, 'name' => 'name'];
        $groupId = 1;
        $field = 'field';
        $path = 'path';
        $scope = 'scope';
        $scopeCode = 'code';
        $oldValue = '';

        $this->model->setValue($value);
        $this->model->setGroupId($groupId);
        $this->model->setField($field);
        $this->model->setPath($path);
        $this->model->setScope($scope);
        $this->model->setScopeCode($scopeCode);

        $this->scopeConfigMock->method('getValue')
            ->with($path, $scope, $scopeCode)
            ->willReturn($oldValue);

        $this->writeMock->expects($this->never())
            ->method('delete');

        $this->assertEquals($this->model, $this->model->beforeSave());
    }
}
