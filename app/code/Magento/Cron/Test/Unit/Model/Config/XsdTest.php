<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Model\Config;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * @var string
     */
    protected $_xsdFile;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->_xsdFile = $urnResolver->getRealPath('urn:magento:module:Magento_Cron:etc/crontab.xsd');
    }

    /**
     * @param string $xmlFile
     * @dataProvider validXmlFileDataProvider
     */
    public function testValidXmlFile($xmlFile)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/_files/{$xmlFile}");
        libxml_use_internal_errors(true);
        $result = Dom::validateDomDocument($dom, $this->_xsdFile);
        libxml_use_internal_errors(false);
        $this->assertEmpty($result, 'Validation failed with errors: ' . join(', ', $result));
    }

    /**
     * @return array
     */
    public function validXmlFileDataProvider()
    {
        return [['crontab_valid.xml'], ['crontab_valid_without_schedule.xml']];
    }

    /**
     * @param string $xmlFile
     * @param array $expectedErrors
     * @dataProvider invalidXmlFileDataProvider
     */
    public function testInvalidXmlFile($xmlFile, $expectedErrors)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/_files/{$xmlFile}");
        libxml_use_internal_errors(true);

        $result = Dom::validateDomDocument($dom, $this->_xsdFile);

        libxml_use_internal_errors(false);
        $this->assertEquals($expectedErrors, $result);
    }

    /**
     * @return array
     */
    public function invalidXmlFileDataProvider()
    {
        return [
            [
                'crontab_invalid.xml',
                [
                    "Element 'job', attribute 'wrongName': The attribute 'wrongName' is not allowed.\nLine: 10\n",
                    "Element 'job', attribute 'wrongInstance': " .
                        "The attribute 'wrongInstance' is not allowed.\nLine: 10\n",
                    "Element 'job', attribute 'wrongMethod': The attribute 'wrongMethod' is not allowed.\nLine: 10\n",
                    "Element 'job': The attribute 'name' is required but missing.\nLine: 10\n",
                    "Element 'job': The attribute 'instance' is required but missing.\nLine: 10\n",
                    "Element 'job': The attribute 'method' is required but missing.\nLine: 10\n",
                    "Element 'wrongSchedule': This element is not expected." .
                        " Expected is one of ( schedule, config_path ).\nLine: 11\n"
                ],
            ],
            [
                'crontab_invalid_duplicates.xml',
                [
                    "Element 'job': Duplicate key-sequence ['job1'] in " .
                        "unique identity-constraint 'uniqueJobName'.\nLine: 13\n"
                ]
            ],
            [
                'crontab_invalid_without_name.xml',
                ["Element 'job': The attribute 'name' is required but missing.\nLine: 10\n"]
            ],
            [
                'crontab_invalid_without_instance.xml',
                ["Element 'job': The attribute 'instance' is required but missing.\nLine: 10\n"]
            ],
            [
                'crontab_invalid_without_method.xml',
                ["Element 'job': The attribute 'method' is required but missing.\nLine: 10\n"]
            ]
        ];
    }
}
