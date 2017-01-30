<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular\Magento\Sales;

class PdfConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider fileFormatDataProvider
     */
    public function testFileFormat($file)
    {
        /** @var \Magento\Sales\Model\Order\Pdf\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Sales\Model\Order\Pdf\Config\SchemaLocator'
        );
        $schemaFile = $schemaLocator->getPerFileSchema();

        $validationStateMock = $this->getMock('\Magento\Framework\Config\ValidationStateInterface', [], [], '', false);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $dom = new \Magento\Framework\Config\Dom(file_get_contents($file), $validationStateMock);
        $result = $dom->validate($schemaFile, $errors);
        $this->assertTrue($result, print_r($errors, true));
    }

    /**
     * @return array
     */
    public function fileFormatDataProvider()
    {
        return \Magento\Framework\App\Utility\Files::init()->getConfigFiles('pdf.xml');
    }

    public function testMergedFormat()
    {
        $validationState = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationState->expects($this->any())->method('isValidationRequired')->will($this->returnValue(true));

        /** @var \Magento\Sales\Model\Order\Pdf\Config\Reader $reader */
        $reader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Order\Pdf\Config\Reader',
            ['validationState' => $validationState]
        );
        try {
            $reader->read();
        } catch (\Exception $e) {
            $this->fail('Merged pdf.xml files do not pass XSD validation: ' . $e->getMessage());
        }
    }
}
