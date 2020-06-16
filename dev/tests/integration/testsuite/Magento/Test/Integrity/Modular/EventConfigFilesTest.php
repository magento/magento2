<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class EventConfigFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_schemaFile = $objectManager->get(\Magento\Framework\Event\Config\SchemaLocator::class)->getSchema();
    }

    /**
     * @param string $file
     * @dataProvider eventConfigFilesDataProvider
     */
    public function testEventConfigFiles($file)
    {
        $errors = [];
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $dom = new \Magento\Framework\Config\Dom(file_get_contents($file), $validationStateMock);
        $result = $dom->validate($this->_schemaFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error->message} Line: {$error->line}\n";
        }
        $this->assertTrue($result, $message);
    }

    /**
     * @return array
     */
    public function eventConfigFilesDataProvider()
    {
        return \Magento\Framework\App\Utility\Files::init()->getConfigFiles('{*/events.xml,events.xml}');
    }
}
