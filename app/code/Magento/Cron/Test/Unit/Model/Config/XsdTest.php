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
    public static function validXmlFileDataProvider()
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
    public static function invalidXmlFileDataProvider()
    {
        return [
            [
                'crontab_invalid.xml',
                [
                    "Element 'job', attribute 'wrongName': The attribute 'wrongName' is not allowed.\nLine: 10\n" .
                    "The xml was: \n5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job wrongName=\"job1\" wrongInstance=\"Model1\" " .
                    "wrongMethod=\"method1\">\n10:            <wrongSchedule>30 2 * * *</wrongSchedule>\n" .
                    "11:        </job>\n12:    </group>\n13:</config>\n14:\n",
                    "Element 'job', attribute 'wrongInstance': The attribute 'wrongInstance' is not allowed.\n" .
                    "Line: 10\nThe xml was: \n5: */\n6:-->\n7:<config " .
                    "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job wrongName=\"job1\" wrongInstance=\"Model1\" " .
                    "wrongMethod=\"method1\">\n10:            <wrongSchedule>30 2 * * *</wrongSchedule>\n" .
                    "11:        </job>\n12:    </group>\n13:</config>\n14:\n",
                    "Element 'job', attribute 'wrongMethod': The attribute 'wrongMethod' is not allowed.\nLine: 10\n" .
                    "The xml was: \n5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job wrongName=\"job1\" wrongInstance=\"Model1\" " .
                    "wrongMethod=\"method1\">\n10:            <wrongSchedule>30 2 * * *</wrongSchedule>\n" .
                    "11:        </job>\n12:    </group>\n13:</config>\n14:\n",
                    "Element 'job': The attribute 'name' is required but missing.\nLine: 10\nThe xml was: \n" .
                    "5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job wrongName=\"job1\" wrongInstance=\"Model1\" " .
                    "wrongMethod=\"method1\">\n10:            <wrongSchedule>30 2 * * *</wrongSchedule>\n" .
                    "11:        </job>\n12:    </group>\n13:</config>\n14:\n",
                    "Element 'job': The attribute 'instance' is required but missing.\nLine: 10\nThe xml was: \n" .
                    "5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job wrongName=\"job1\" wrongInstance=\"Model1\" " .
                    "wrongMethod=\"method1\">\n10:            <wrongSchedule>30 2 * * *</wrongSchedule>\n" .
                    "11:        </job>\n12:    </group>\n13:</config>\n14:\n",
                    "Element 'job': The attribute 'method' is required but missing.\nLine: 10\nThe xml was: \n" .
                    "5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job wrongName=\"job1\" wrongInstance=\"Model1\" " .
                    "wrongMethod=\"method1\">\n10:            <wrongSchedule>30 2 * * *</wrongSchedule>\n" .
                    "11:        </job>\n12:    </group>\n13:</config>\n14:\n",
                    "Element 'wrongSchedule': This element is not expected. Expected is one of ( schedule, " .
                    "config_path ).\nLine: 11\nThe xml was: \n6:-->\n7:<config " .
                    "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job wrongName=\"job1\" wrongInstance=\"Model1\" " .
                    "wrongMethod=\"method1\">\n10:            <wrongSchedule>30 2 * * *</wrongSchedule>\n" .
                    "11:        </job>\n12:    </group>\n13:</config>\n14:\n"
                ],
            ],
            [
                'crontab_invalid_duplicates.xml',
                [
                    "Element 'job': Duplicate key-sequence ['job1'] in unique identity-constraint 'uniqueJobName'.\n" .
                    "Line: 13\nThe xml was: \n8:    <group id=\"default\">\n9:        <job name=\"job1\" " .
                    "instance=\"Model1\" method=\"method1\">\n10:            <schedule>30 2 * * *</schedule>\n" .
                    "11:        </job>\n12:        <job name=\"job1\" instance=\"Model1\" method=\"method1\">\n" .
                    "13:            <schedule>30 2 * * *</schedule>\n14:        </job>\n15:    </group>\n" .
                    "16:</config>\n17:\n"
                ]
            ],
            [
                'crontab_invalid_without_name.xml',
                [
                    "Element 'job': The attribute 'name' is required but missing.\nLine: 10\nThe xml was: \n" .
                    "5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job instance=\"Model1\" method=\"method1\">\n" .
                    "10:            <schedule>30 2 * * *</schedule>\n11:        </job>\n12:    </group>\n" .
                    "13:</config>\n14:\n"
                ]
            ],
            [
                'crontab_invalid_without_instance.xml',
                [
                    "Element 'job': The attribute 'instance' is required but missing.\nLine: 10\nThe xml was: \n" .
                    "5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job name=\"job1\" method=\"method1\">\n" .
                    "10:            <schedule>30 2 * * *</schedule>\n11:        </job>\n12:    </group>\n" .
                    "13:</config>\n14:\n"
                ]
            ],
            [
                'crontab_invalid_without_method.xml',
                [
                    "Element 'job': The attribute 'method' is required but missing.\nLine: 10\nThe xml was: \n" .
                    "5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Cron:etc/crontab.xsd\">\n" .
                    "8:    <group id=\"default\">\n9:        <job name=\"job1\" instance=\"Model1\">\n" .
                    "10:            <schedule>30 2 * * *</schedule>\n11:        </job>\n12:    </group>\n" .
                    "13:</config>\n14:\n"
                ]
            ]
        ];
    }
}
