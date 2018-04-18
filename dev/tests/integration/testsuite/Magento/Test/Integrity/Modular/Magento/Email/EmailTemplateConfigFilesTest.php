<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular\Magento\Email;

class EmailTemplateConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that email template configuration file matches the format
     *
     * @param string $file
     * @dataProvider fileFormatDataProvider
     */
    public function testFileFormat($file)
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $schemaFile = $urnResolver->getRealPath('urn:magento:module:Magento_Email:etc/email_templates.xsd');
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
        return \Magento\Framework\App\Utility\Files::init()->getConfigFiles('email_templates.xml');
    }

    /**
     * Test that email template configuration contains references to existing template files
     *
     * @param string $templateId
     * @dataProvider templateReferenceDataProvider
     */
    public function testTemplateReference($templateId)
    {
        /** @var \Magento\Email\Model\Template\Config $emailConfig */
        $emailConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Config'
        );

        $parts = $emailConfig->parseTemplateIdParts($templateId);
        $templateId = $parts['templateId'];

        $designParams = [];
        $theme = $parts['theme'];
        if ($theme) {
            $designParams['theme'] = $theme;
        }

        $templateFilename = $emailConfig->getTemplateFilename($templateId, $designParams);
        $this->assertFileExists($templateFilename, 'Email template file, specified in the configuration, must exist');
    }

    /**
     * @return array
     */
    public function templateReferenceDataProvider()
    {
        $data = [];
        /** @var \Magento\Email\Model\Template\Config $emailConfig */
        $emailConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Config'
        );
        foreach ($emailConfig->getAvailableTemplates() as $template) {
            $data[$template['value']] = [$template['value']];
        }
        return $data;
    }

    /**
     * Test that merged configuration of email templates matches the format
     */
    public function testMergedFormat()
    {
        $validationState = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationState->expects($this->any())->method('isValidationRequired')->will($this->returnValue(true));
        /** @var \Magento\Email\Model\Template\Config\Reader $reader */
        $reader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Config\Reader',
            ['validationState' => $validationState]
        );
        try {
            $reader->read();
        } catch (\Exception $e) {
            $this->fail('Merged email templates configuration does not pass XSD validation: ' . $e->getMessage());
        }
    }
}
