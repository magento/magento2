<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Pack\Writer\File;

use Magento\Setup\Module\I18n\Context;
use Magento\Setup\Module\I18n\Locale;
use Magento\Setup\Module\I18n\Dictionary;
use Magento\Setup\Module\I18n\Factory;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Pack\Writer\File\Csv;
use Magento\Setup\Module\I18n\Dictionary\WriterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

require_once __DIR__ . '/_files/ioMock.php';

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CsvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Locale|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeMock;

    /**
     * @var Dictionary|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dictionaryMock;

    /**
     * @var Phrase|\PHPUnit_Framework_MockObject_MockObject
     */
    private $phraseMock;

    /**
     * @var Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * @var Csv|\PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    /**
     * @return void
     */
    protected function setUp()
    {
        /** @var ObjectManagerHelper $objectManagerHelper */
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->getMock(Context::class, [], [], '', false, false);
        $this->localeMock = $this->getMock(Locale::class, [], [], '', false, false);
        $this->dictionaryMock = $this->getMock(Dictionary::class, [], [], '', false, false);
        $this->phraseMock = $this->getMock(Phrase::class, [], [], '', false, false);
        $this->factoryMock = $this->getMock(Factory::class, [], [], '', false, false);

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
     * @expectedException \RuntimeException
     * @return void
     */
    public function testWriteDictionaryWithRuntimeException($contextType, $contextValue)
    {
        $this->configureGeneralPhrasesMock($contextType, $contextValue);

        $this->object->writeDictionary($this->dictionaryMock, $this->localeMock);
    }

    /**
     * @return array
     */
    public function writeDictionaryWithRuntimeExceptionDataProvider()
    {
        return [
            ['', []],
            ['module', []],
            ['', ['Magento_Module']]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Some error. Row #1.
     * @return void
     */
    public function testWriteDictionaryWithInvalidArgumentException()
    {
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
