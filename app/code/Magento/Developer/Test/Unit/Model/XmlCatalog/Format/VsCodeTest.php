<?php

namespace Magento\Developer\Test\Unit\Model\XmlCatalog\Format;

use Magento\Developer\Model\XmlCatalog\Format\VsCode;
use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\WriteFactory;

class VsCodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Magento\Developer\Model\XmlCatalog\Format\VsCode
     */
    protected $vscodeFormat;

    /**
     * @var Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var Magento\Framework\Filesystem\File\WriteFactory
     */
    protected $fileWriteFactory;

    /**
     * @var Magento\Framework\DomDocument\DomDocumentFactory
     */
    protected $domFactory;

    protected $dictionary = [
        'urn:magento:framework:Acl/etc/acl.xsd' => 'vendor/magento/framework/Acl/etc/acl.xsd',
        'urn:magento:module:Magento_Store:etc/config.xsd' => 'vendor/magento/module-store/etc/config.xsd',
        'urn:magento:module:Magento_Cron:etc/crontab.xsd' => 'vendor/magento/module-cron/etc/crontab.xsd',
        'urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd' => 'vendor/magento/framework/Setup/Declaration/Schema/etc/schema.xsd',
    ];

    public function setUp()
    {

        $currentDirRead = $this->createMock(ReadInterface::class);
        $currentDirRead->expects($this->any())
            ->method('getRelativePath')
            ->willReturnCallback(function ($xsdPath) {
                return $xsdPath;
            });

        $this->readFactory = $this->createMock(ReadFactory::class);
        $this->readFactory->expects($this->once())
            ->method('create')
            ->withAnyParameters()
            ->willReturn($currentDirRead);

        $this->fileWriteFactory = $this->createMock(WriteFactory::class);
        $this->domFactory = new DomDocumentFactory();

        $this->vscodeFormat = new VsCode(
            $this->readFactory,
            $this->fileWriteFactory,
            $this->domFactory
        );
    }

    public function testGenerateNewValidCatalog()
    {
        $configFile = 'test';
        $fixtureXmlFile = __DIR__ . '/_files/valid_catalog.xml';
        $content = file_get_contents($fixtureXmlFile);

        $message = __("The \"%1.xml\" file doesn't exist.", $configFile);

        $this->fileWriteFactory->expects($this->at(0))
            ->method('create')
            ->with(
                $configFile,
                DriverPool::FILE,
                VsCode::FILE_MODE_READ
            )
            ->willThrowException(new FileSystemException($message));

        $file = $this->createMock(\Magento\Framework\Filesystem\File\Write::class);
        $file->expects($this->once())
            ->method('write')
            ->with($content);

        $this->fileWriteFactory->expects($this->at(1))
            ->method('create')
            ->with(
                $configFile,
                DriverPool::FILE,
                VsCode::FILE_MODE_WRITE
            )
            ->willReturn($file);

        $this->vscodeFormat->generateCatalog($this->dictionary, $configFile);
    }

    public function testGenerateExistingValidCatalog()
    {
        $configFile = 'test';
        $fixtureXmlFile = __DIR__ . '/_files/valid_catalog.xml';
        $content = file_get_contents($fixtureXmlFile);

        $file = $this->createMock(\Magento\Framework\Filesystem\File\Read::class);
        $file->expects($this->once())
            ->method('readAll')
            ->withAnyParameters()
            ->willReturn($content);

        $this->fileWriteFactory->expects($this->at(0))
            ->method('create')
            ->with(
                $configFile,
                DriverPool::FILE,
                VsCode::FILE_MODE_READ
            )
            ->willReturn($file);

        $file = $this->createMock(\Magento\Framework\Filesystem\File\Write::class);
        $file->expects($this->once())
            ->method('write')
            ->with($content);

        $this->fileWriteFactory->expects($this->at(1))
            ->method('create')
            ->with(
                $configFile,
                DriverPool::FILE,
                VsCode::FILE_MODE_WRITE
            )
            ->willReturn($file);

        $this->vscodeFormat->generateCatalog($this->dictionary, $configFile);
    }

    public function testGenerateExistingEmptyValidCatalog()
    {
        $configFile = 'test';
        $fixtureXmlFile = __DIR__ . '/_files/valid_catalog.xml';
        $content = file_get_contents($fixtureXmlFile);

        $file = $this->createMock(\Magento\Framework\Filesystem\File\Read::class);
        $file->expects($this->once())
            ->method('readAll')
            ->withAnyParameters()
            ->willReturn('');

        $this->fileWriteFactory->expects($this->at(0))
            ->method('create')
            ->with(
                $configFile,
                DriverPool::FILE,
                VsCode::FILE_MODE_READ
            )
            ->willReturn($file);

        $file = $this->createMock(\Magento\Framework\Filesystem\File\Write::class);
        $file->expects($this->once())
            ->method('write')
            ->with($content);

        $this->fileWriteFactory->expects($this->at(1))
            ->method('create')
            ->with(
                $configFile,
                DriverPool::FILE,
                VsCode::FILE_MODE_WRITE
            )
            ->willReturn($file);

        $this->vscodeFormat->generateCatalog($this->dictionary, $configFile);
    }

    public function testGenerateExistingInvalidValidCatalog()
    {
        $configFile = 'test';
        $invalidXmlFile = __DIR__ . '/_files/invalid_catalog.xml';
        $invalidContent = file_get_contents($invalidXmlFile);
        $validXmlFile = __DIR__ . '/_files/valid_catalog.xml';
        $validContent = file_get_contents($validXmlFile);

        $file = $this->createMock(\Magento\Framework\Filesystem\File\Read::class);
        $file->expects($this->once())
            ->method('readAll')
            ->withAnyParameters()
            ->willReturn($invalidContent);

        $this->fileWriteFactory->expects($this->at(0))
            ->method('create')
            ->with(
                $configFile,
                DriverPool::FILE,
                VsCode::FILE_MODE_READ
            )
            ->willReturn($file);

        $file = $this->createMock(\Magento\Framework\Filesystem\File\Write::class);
        $file->expects($this->once())
            ->method('write')
            ->with($validContent);

        $this->fileWriteFactory->expects($this->at(1))
            ->method('create')
            ->with(
                $configFile,
                DriverPool::FILE,
                VsCode::FILE_MODE_WRITE
            )
            ->willReturn($file);

        $this->vscodeFormat->generateCatalog($this->dictionary, $configFile);
    }
}
