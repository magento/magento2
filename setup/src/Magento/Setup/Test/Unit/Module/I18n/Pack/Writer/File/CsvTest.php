<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Pack\Writer\File;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Module\I18n\Context;
use Magento\Setup\Module\I18n\Dictionary;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Dictionary\WriterInterface;
use Magento\Setup\Module\I18n\Factory;
use Magento\Setup\Module\I18n\Locale;
use Magento\Setup\Module\I18n\Pack\Writer\File\Csv;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/ioMock.php';

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CsvTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Locale|MockObject
     */
    private $localeMock;

    /**
     * @var Dictionary|MockObject
     */
    private $dictionaryMock;

    /**
     * @var Phrase|MockObject
     */
    private $phraseMock;

    /**
     * @var Factory|MockObject
     */
    private $factoryMock;

    /**
     * @var Csv|MockObject
     */
    private $object;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        /** @var ObjectManagerHelper $objectManagerHelper */
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->createMock(Context::class);
        $this->localeMock = $this->createMock(Locale::class);
        $this->dictionaryMock = $this->createMock(Dictionary::class);
        $this->phraseMock = $this->createMock(Phrase::class);
        $this->factoryMock = $this->createMock(Factory::class);

        $constructorArguments = $objectManagerHelper->getConstructArguments(
            Csv::class,
            [
                'context' => $this->contextMock,
                'factory' => $this->factoryMock
            ]
        );
        $this->object = $objectManagerHelper->getObject(Csv::class, $constructorArguments);
    }

    /**
     * @param string $contextType
     * @param array $contextValue
     * @dataProvider writeDictionaryWithRuntimeExceptionDataProvider
     * @return void
     */
    public function testWriteDictionaryWithRuntimeException($contextType, $contextValue)
    {
        $this->expectException('RuntimeException');
        $this->configureGeneralPhrasesMock($contextType, $contextValue);

        $this->object->writeDictionary($this->dictionaryMock, $this->localeMock);
    }

    /**
     * @return array
     */
    public static function writeDictionaryWithRuntimeExceptionDataProvider()
    {
        return [
            ['', []],
            ['module', []],
            ['', ['Magento_Module']]
        ];
    }

    /**
     * @return void
     */
    public function testWriteDictionaryWithInvalidArgumentException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Some error. Row #1.');
        $contextType = 'module';
        $contextValue = 'Magento_Module';

        $this->configureGeneralPhrasesMock($contextType, [$contextValue]);

        $this->contextMock->expects($this->once())
            ->method('buildPathToLocaleDirectoryByContext')
            ->with($contextType, $contextValue)
            ->willThrowException(new \InvalidArgumentException('Some error.'));

        $this->object->writeDictionary($this->dictionaryMock, $this->localeMock);
    }

    /**
     * @return void
     */
    public function testWriteDictionaryWherePathIsNull()
    {
        $contextType = 'module';
        $contextValue = 'Magento_Module';

        $this->configureGeneralPhrasesMock($contextType, [$contextValue]);

        $this->contextMock->expects($this->once())
            ->method('buildPathToLocaleDirectoryByContext')
            ->with($contextType, $contextValue)
            ->willReturn(null);

        $this->phraseMock->expects($this->never())
            ->method('setContextType');
        $this->phraseMock->expects($this->never())
            ->method('setContextValue');

        $this->object->writeDictionary($this->dictionaryMock, $this->localeMock);
    }

    /**
     * @return void
     */
    public function testWriteDictionary()
    {
        $contextType = 'module';
        $contextValue = 'Magento_Module';
        $path = '/some/path/';
        $phrase = 'Phrase';
        $locale = 'en_EN';
        $file = $path . $locale . '.' . Csv::FILE_EXTENSION;

        $this->configureGeneralPhrasesMock($contextType, [$contextValue]);

        $this->phraseMock->expects($this->once())
            ->method('getPhrase')
            ->willReturn($phrase);
        $this->phraseMock->expects($this->once())
            ->method('setContextType')
            ->with(null);
        $this->phraseMock->expects($this->once())
            ->method('setContextValue')
            ->with(null);
        $this->localeMock->expects($this->once())
            ->method('__toString')
            ->willReturn($locale);

        $this->contextMock->expects($this->once())
            ->method('buildPathToLocaleDirectoryByContext')
            ->with($contextType, $contextValue)
            ->willReturn($path);

        $writerMock = $this->getMockForAbstractClass(WriterInterface::class);
        $writerMock->expects($this->once())
            ->method('write')
            ->with($this->phraseMock);
        $this->factoryMock->expects($this->once())
            ->method('createDictionaryWriter')
            ->with($file)
            ->willReturn($writerMock);

        $this->object->writeDictionary($this->dictionaryMock, $this->localeMock);
    }

    /**
     * @param string $contextType
     * @param array $contextValue
     * @return void
     */
    private function configureGeneralPhrasesMock($contextType, $contextValue)
    {
        $this->phraseMock->expects($this->any())
            ->method('getContextType')
            ->willReturn($contextType);
        $this->phraseMock->expects($this->any())
            ->method('getContextValue')
            ->willReturn($contextValue);

        $this->dictionaryMock->expects($this->once())
            ->method('getPhrases')
            ->willReturn([$this->phraseMock]);
    }
}
