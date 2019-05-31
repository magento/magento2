<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Config\Structure\Reader;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Config\Model\Config\SchemaLocator;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\Dom;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Class ReaderTest check Magento\Config\Model\Config\Structure\Reader::_readFiles() method.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Reader
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileResolver;

    /**
     * Test config location.
     *
     * @string
     */
    const CONFIG = '/dev/tests/integration/testsuite/Magento/Config/Model/Config/Structure/Reader/_files/';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Files
     */
    private $fileUtility;

    /**
     * @var ValidationStateInterface
     */
    private $validationStateMock;

    /**
     * @var \Magento\Framework\Config\SchemaLocatorInterface
     */
    private $schemaLocatorMock;

    /**
     * @var FileResolverInterface
     */
    private $fileResolverMock;

    /**
     * @var ReaderStub
     */
    private $reader;

    /**
     * @var ConverterStub
     */
    private $converter;

    /**
     * @var CompilerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $compiler;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->fileResolver = $this->getMockForAbstractClass(FileResolverInterface::class);
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->create(
            \Magento\Widget\Model\Config\Reader::class,
            ['fileResolver' => $this->fileResolver]
        );
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileUtility = Files::init();
        $this->fileResolverMock = $this->getMockBuilder(FileResolverInterface::class)
            ->getMockForAbstractClass();
        $this->converter = $this->objectManager->create(ConverterStub::class);

        //Isolate test from actual configuration, and leave only sample data.
        $this->compiler = $this->getMockBuilder(CompilerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile'])
            ->getMockForAbstractClass();
    }

    /**
     * The test checks the file structure after processing the nodes responsible for inserting content.
     *
     * @return void
     */
    public function testXmlConvertedConfigurationAndCompereStructure()
    {
        $this->validationStateMock = $this->getMockBuilder(ValidationStateInterface::class)
            ->setMethods(['isValidationRequired'])
            ->getMockForAbstractClass();
        $this->validationStateMock->expects($this->atLeastOnce())
            ->method('isValidationRequired')
            ->willReturn(false);
        $this->schemaLocatorMock = $this->getMockBuilder(SchemaLocator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPerFileSchema'])
            ->getMock();
        $this->reader = $this->objectManager->create(
            ReaderStub::class,
            [
                'fileResolver' => $this->fileResolverMock,
                'converter' => $this->converter,
                'schemaLocator' => $this->schemaLocatorMock,
                'validationState' => $this->validationStateMock,
                'fileName' => 'no_existing_file.xml',
                'compiler' => $this->compiler,
                'domDocumentClass' => Dom::class
            ]
        );
        $actual = $this->reader->readFiles(['actual' => $this->getContent()]);

        $document = new \DOMDocument();
        $document->loadXML($this->getContent());

        $expected = $this->converter->getArrayData($document);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Get config sample data for test.
     *
     * @return string
     */
    protected function getContent()
    {
        $files = $this->fileUtility->getFiles([BP . static::CONFIG], 'config.xml');

        return file_get_contents(reset($files));
    }

    /**
     * Checks method read() to get correct config.
     *
     */
    public function testRead()
    {
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->willReturn([file_get_contents(__DIR__ . '/_files/orders_and_returns.xml')]);
        $expected = include __DIR__ . '/_files/expectedGlobalArray.php';
        $this->assertEquals($expected, $this->model->read('global'));
    }

    /**
     * Checks method _readFiles() to get correct config.
     *
     */
    public function testReadFile()
    {
        $file = file_get_contents(__DIR__ . '/_files/orders_and_returns.xml');
        $expected = include __DIR__ . '/_files/expectedGlobalArray.php';
        $this->assertEquals($expected, $this->model->readFile($file));
    }

    /**
     * Checks method _readFiles() to get correct config with merged configs.
     *
     */
    public function testMergeCompleteAndPartial()
    {
        $fileList = [
            file_get_contents(__DIR__ . '/_files/catalog_new_products_list.xml'),
            file_get_contents(__DIR__ . '/_files/orders_and_returns_customized.xml'),
        ];
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('widget.xml', 'global')
            ->willReturn($fileList);
        $expected = include __DIR__ . '/_files/expectedMergedArray.php';
        $this->assertEquals($expected, $this->model->read('global'));
    }
}
