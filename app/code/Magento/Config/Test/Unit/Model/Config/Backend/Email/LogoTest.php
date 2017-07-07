<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LogoTest extends \PHPUnit_Framework_TestCase
{
    /** @var Logo */
    protected $model;

    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    /** @var TypeListInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $typeListMock;

    /** @var UploaderFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uploaderFactoryMock;

    /** @var RequestDataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestDataMock;

    /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    /** @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $writeMock;

    /** @var Uploader|\PHPUnit_Framework_MockObject_MockObject */
    protected $uploaderMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->typeListMock = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->getMockForAbstractClass();
        $this->uploaderFactoryMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\UploaderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->requestDataMock
            = $this->getMockBuilder(\Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface::class)
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writeMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->getMock();
        $this->uploaderMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->uploaderFactoryMock->expects($this->any())
            ->method('create')
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

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with($path, $scope, $scopeCode)
            ->willReturn($oldValue);

        $this->writeMock->expects($this->once())
            ->method('delete')
            ->with(Logo::UPLOAD_DIR . '/' . $oldValue)
            ->willReturn(true);

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

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with($path, $scope, $scopeCode)
            ->willReturn($oldValue);

        $this->writeMock->expects($this->once())
            ->method('delete')
            ->with(Logo::UPLOAD_DIR . '/' . $oldValue)
            ->willReturn(true);

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

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
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

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with($path, $scope, $scopeCode)
            ->willReturn($oldValue);

        $this->writeMock->expects($this->never())
            ->method('delete');

        $this->assertEquals($this->model, $this->model->beforeSave());
    }
}
