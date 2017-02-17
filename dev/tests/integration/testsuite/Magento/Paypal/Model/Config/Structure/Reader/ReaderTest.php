<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Structure\Reader;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ReaderTest
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED = '/dev/tests/integration/testsuite/Magento/Paypal/Model/Config/Structure/Reader/_files/expected';

    const ACTUAL = '/dev/tests/integration/testsuite/Magento/Paypal/Model/Config/Structure/Reader/_files/actual';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Utility\Files
     */
    protected $fileUtility;

    /**
     * @var \Magento\Framework\Config\ValidationStateInterface
     */
    protected $validationStateMock;

    /**
     * @var \Magento\Framework\Config\SchemaLocatorInterface
     */
    protected $schemaLocatorMock;

    /**
     * @var \Magento\Framework\Config\FileResolverInterface
     */
    protected $fileResolverMock;

    /**
     * @var \Magento\Paypal\Model\Config\Structure\Reader\ReaderStub
     */
    protected $reader;

    /**
     * @var \Magento\Paypal\Model\Config\Structure\Reader\ConverterStub
     */
    protected $converter;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileUtility = \Magento\Framework\App\Utility\Files::init();

        $this->validationStateMock = $this->getMockBuilder(\Magento\Framework\Config\ValidationStateInterface::class)
            ->setMethods(['isValidationRequired'])
            ->getMockForAbstractClass();
        $this->schemaLocatorMock = $this->getMockBuilder(\Magento\Config\Model\Config\SchemaLocator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPerFileSchema'])
            ->getMock();
        $this->fileResolverMock = $this->getMockBuilder(\Magento\Framework\Config\FileResolverInterface::class)
            ->getMockForAbstractClass();

        $this->validationStateMock->expects($this->atLeastOnce())
            ->method('isValidationRequired')
            ->willReturn(false);
        $this->schemaLocatorMock->expects($this->atLeastOnce())
            ->method('getPerFileSchema')
            ->willReturn(false);

        /** @var \Magento\Paypal\Model\Config\Structure\Reader\ConverterStub $converter */
        $this->converter = $this->objectManager->create(
            \Magento\Paypal\Model\Config\Structure\Reader\ConverterStub::class
        );

        $this->reader = $this->objectManager->create(
            \Magento\Paypal\Model\Config\Structure\Reader\ReaderStub::class,
            [
                'fileResolver' => $this->fileResolverMock,
                'converter' => $this->converter,
                'schemaLocator' => $this->schemaLocatorMock,
                'validationState' => $this->validationStateMock,
                'fileName' => 'no_existing_file.xml',
                'domDocumentClass' => \Magento\Framework\Config\Dom::class
            ]
        );
    }

    /**
     *  The test checks the file structure after processing the nodes responsible for inserting content
     *
     * @return void
     */
    public function testXmlConvertedConfigurationAndCompereStructure()
    {
        $actual = $this->reader->readFiles(['actual' => $this->getActualContent()]);

        $document = new \DOMDocument();
        $document->loadXML($this->getExpectedContent());

        $expected = $this->converter->getArrayData($document);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return string
     */
    protected function getActualContent()
    {
        $files = $this->fileUtility->getFiles([BP . static::ACTUAL], 'config.xml');

        return file_get_contents(reset($files));
    }

    /**
     * @return string
     */
    protected function getExpectedContent()
    {
        $files = $this->fileUtility->getFiles([BP . static::EXPECTED], 'config.xml');

        return file_get_contents(reset($files));
    }
}
