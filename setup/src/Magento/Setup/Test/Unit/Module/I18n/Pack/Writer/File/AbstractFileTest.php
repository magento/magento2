<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Pack\Writer\File;

use Magento\Setup\Module\I18n\Context;
use Magento\Setup\Module\I18n\Locale;
use Magento\Setup\Module\I18n\Dictionary;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Pack\Writer\File\AbstractFile;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Tests for Magento\Setup\Module\I18n\Pack\Writer\File\AbstractFile
 */
class AbstractFileTest extends \PHPUnit_Framework_TestCase
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
     * @var AbstractFile|\PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->getMock(Context::class, [], [], '', false, false);
        $this->localeMock = $this->getMock(Locale::class, [], [], '', false, false);
        $this->dictionaryMock = $this->getMock(Dictionary::class, [], [], '', false, false);
        $this->phraseMock = $this->getMock(Phrase::class, [], [], '', false, false);

        $constructorArguments = $objectManagerHelper->getConstructArguments(
            AbstractFile::class,
            ['context' => $this->contextMock]
        );

        $this->object = $this->getMockBuilder(AbstractFile::class)
            ->setMethods(['_createDirectoryIfNotExist', '_writeFile'])
            ->setConstructorArgs($constructorArguments)
            ->getMockForAbstractClass();
    }

    /**
     * @param string $contextType
     * @param array $contextValue
     * @dataProvider writeDictionaryWithRuntimeExceptionDataProvider
     * @expectedException \RuntimeException
     * @return void
     */
    public function testWriteDictionaryWithRuntimeException(
        $contextType,
        array $contextValue
    ) {
        $this->configureGeneralPhrasesMock($contextType, $contextValue);

        $this->object->expects($this->never())
            ->method('_createDirectoryIfNotExist');
        $this->object->expects($this->never())
            ->method('_writeFile');
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
            ['', ['Magento_Module']],
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

        $this->object->expects($this->never())
            ->method('_createDirectoryIfNotExist');
        $this->object->expects($this->never())
            ->method('_writeFile');

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

        $this->object->expects($this->never())
            ->method('_createDirectoryIfNotExist');
        $this->object->expects($this->never())
            ->method('_writeFile');

        $this->contextMock->expects($this->once())
            ->method('buildPathToLocaleDirectoryByContext')
            ->with($contextType, $contextValue)
            ->willReturn(null);

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
        $fileExtension = 'csv';
        $file = $path . $locale . '.' . $fileExtension;

        $this->configureGeneralPhrasesMock($contextType, [$contextValue]);

        $this->phraseMock->expects($this->once())
            ->method('getPhrase')
            ->willReturn($phrase);

        $this->localeMock->expects($this->once())
            ->method('__toString')
            ->willReturn($locale);

        $this->object->expects($this->once())
            ->method('_getFileExtension')
            ->willReturn($fileExtension);
        $this->object->expects($this->once())
            ->method('_createDirectoryIfNotExist')
            ->with(dirname($file));
        $this->object->expects($this->once())
            ->method('_writeFile')
            ->with($file, [$phrase => $this->phraseMock]);

        $this->contextMock->expects($this->once())
            ->method('buildPathToLocaleDirectoryByContext')
            ->with($contextType, $contextValue)
            ->willReturn($path);

        $this->object->writeDictionary($this->dictionaryMock, $this->localeMock);
    }

    /**
     * @param string $contextType
     * @param array $contextValue
     * @return void
     */
    private function configureGeneralPhrasesMock($contextType, array $contextValue)
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
