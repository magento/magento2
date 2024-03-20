<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Test\Unit\Model\XmlCatalog\Format;

use Magento\Developer\Model\XmlCatalog\Format\VsCode;
use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VsCodeTest extends TestCase
{
    /**
     * @var VsCode
     */
    private $vscodeFormat;

    /**
     * @var MockObject|ReadFactory
     */
    private $readFactoryMock;

    /**
     * @var MockObject|WriteFactory
     */
    private $fileWriteFactoryMock;

    /**
     * @var DomDocumentFactory
     */
    private $domFactory;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $currentDirReadMock = $this->getMockForAbstractClass(ReadInterface::class);
        $currentDirReadMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturnCallback(function ($xsdPath) {
                return $xsdPath;
            });

        $this->readFactoryMock = $this->createMock(ReadFactory::class);
        $this->readFactoryMock->expects($this->once())
            ->method('create')
            ->withAnyParameters()
            ->willReturn($currentDirReadMock);

        $this->fileWriteFactoryMock = $this->createMock(WriteFactory::class);
        $this->domFactory = $this->objectManagerHelper->getObject(DomDocumentFactory::class);

        $vscodeFormatArgs = $this->objectManagerHelper->getConstructArguments(
            VsCode::class,
            [
                'readFactory' => $this->readFactoryMock,
                'fileWriteFactory' => $this->fileWriteFactoryMock,
                'domDocumentFactory' => $this->domFactory
            ]
        );

        $this->vscodeFormat = $this->objectManagerHelper->getObject(VsCode::class, $vscodeFormatArgs);
    }

    /**
     * Test generation of new valid catalog.
     *
     * @param string $content
     * @param array $dictionary
     *
     * @return void
     * @dataProvider dictionaryDataProvider
     */
    public function testGenerateNewValidCatalog($content, $dictionary): void
    {
        $configFile = 'test';

        $message = __("The \"%1.xml\" file doesn't exist.", $configFile);

        $fileMock = $this->createMock(Write::class);
        $fileMock->expects($this->once())
            ->method('write')
            ->with($content);

        $this->fileWriteFactoryMock
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($configFile, $fileMock, $message) {
                if ($arg1 === $configFile && $arg2 === DriverPool::FILE && $arg3 === VsCode::FILE_MODE_READ) {
                    throw new FileSystemException($message);
                } elseif ($arg1 === $configFile && $arg2 === DriverPool::FILE && $arg3 === VsCode::FILE_MODE_WRITE) {
                    return $fileMock;
                }
            });

        $this->vscodeFormat->generateCatalog($dictionary, $configFile);
    }

    /**
     * Test modify existing valid catalog.
     *
     * @param string $content
     * @param array $dictionary
     *
     * @return void
     * @dataProvider dictionaryDataProvider
     */
    public function testGenerateExistingValidCatalog($content, $dictionary): void
    {
        $configFile = 'test';

        $fileMock1 = $this->createMock(Read::class);
        $fileMock1->expects($this->once())
            ->method('readAll')
            ->withAnyParameters()
            ->willReturn($content);

        $fileMock2 = $this->createMock(Write::class);
        $fileMock2->expects($this->once())
            ->method('write')
            ->with($content);

        $this->fileWriteFactoryMock
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($configFile, $fileMock1, $fileMock2) {
                if ($arg1 === $configFile && $arg2 === DriverPool::FILE && $arg3 === VsCode::FILE_MODE_READ) {
                    return $fileMock1;
                } elseif ($arg1 === $configFile && $arg2 === DriverPool::FILE && $arg3 === VsCode::FILE_MODE_WRITE) {
                    return $fileMock2;
                }
            });

        $this->vscodeFormat->generateCatalog($dictionary, $configFile);
    }

    /**
     * Test modify existing empty catalog.
     *
     * @param string $content
     * @param array $dictionary
     *
     * @return void
     * @dataProvider dictionaryDataProvider
     */
    public function testGenerateExistingEmptyValidCatalog($content, $dictionary): void
    {
        $configFile = 'test';

        $fileMock1 = $this->createMock(Read::class);
        $fileMock1->expects($this->once())
            ->method('readAll')
            ->withAnyParameters()
            ->willReturn('');

        $fileMock2 = $this->createMock(Write::class);
        $fileMock2->expects($this->once())
            ->method('write')
            ->with($content);

        $this->fileWriteFactoryMock
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($configFile, $fileMock1, $fileMock2) {
                if ($arg1 === $configFile && $arg2 === DriverPool::FILE && $arg3 === VsCode::FILE_MODE_READ) {
                    return $fileMock1;
                } elseif ($arg1 === $configFile && $arg2 === DriverPool::FILE && $arg3 === VsCode::FILE_MODE_WRITE) {
                    return $fileMock2;
                }
            });

        $this->vscodeFormat->generateCatalog($dictionary, $configFile);
    }

    /**
     * Test modify existing invalid catalog.
     *
     * @param string $content
     * @param array $dictionary
     *
     * @return void
     * @dataProvider dictionaryDataProvider
     */
    public function testGenerateExistingInvalidValidCatalog($content, $dictionary, $invalidContent): void
    {
        $configFile = 'test';

        $fileMock1 = $this->createMock(Read::class);
        $fileMock1->expects($this->once())
            ->method('readAll')
            ->withAnyParameters()
            ->willReturn($invalidContent);

        $fileMock2 = $this->createMock(Write::class);
        $fileMock2->expects($this->once())
            ->method('write')
            ->with($content);

        $this->fileWriteFactoryMock
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($configFile, $fileMock1, $fileMock2) {
                if ($arg1 === $configFile && $arg2 === DriverPool::FILE && $arg3 === VsCode::FILE_MODE_READ) {
                    return $fileMock1;
                } elseif ($arg1 === $configFile && $arg2 === DriverPool::FILE && $arg3 === VsCode::FILE_MODE_WRITE) {
                    return $fileMock2;
                }
            });

        $this->vscodeFormat->generateCatalog($dictionary, $configFile);
    }

    /**
     * Data provider for test.
     *
     * @return array
     */
    public static function dictionaryDataProvider(): array
    {
        $fixtureXmlFile = __DIR__ . '/_files/valid_catalog.xml';
        $content = file_get_contents($fixtureXmlFile);
        $invalidXmlFile = __DIR__ . '/_files/invalid_catalog.xml';
        $invalidContent = file_get_contents($invalidXmlFile);

        return [
            [
                $content,
                [
                    'urn:magento:framework:Acl/etc/acl.xsd' => 'vendor/magento/framework/Acl/etc/acl.xsd',
                    'urn:magento:module:Magento_Store:etc/config.xsd' => 'vendor/magento/module-store/etc/config.xsd',
                    'urn:magento:module:Magento_Cron:etc/crontab.xsd' => 'vendor/magento/module-cron/etc/crontab.xsd',
                    'urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd' =>
                        'vendor/magento/framework/Setup/Declaration/Schema/etc/schema.xsd'
                ],
                $invalidContent
            ]
        ];
    }
}
