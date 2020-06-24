<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\Config\Converter;

use Magento\Analytics\ReportXml\Config\Converter\Xml;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * A unit test for testing of the reports configuration converter (XML to PHP array).
 */
class XmlTest extends TestCase
{
    /**
     * @var Xml
     */
    private $subject;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper =
            new ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            Xml::class
        );
    }

    /**
     * @return void
     */
    public function testConvertNoElements()
    {
        $this->assertEmpty(
            $this->subject->convert(new \DOMDocument())
        );
    }

    /**
     * @return void
     */
    public function testConvert()
    {
        $dom = new \DOMDocument();

        $expectedArray = [
            'config' => [
                [
                    'noNamespaceSchemaLocation' => 'urn:magento:module:Magento_Analytics:etc/reports.xsd',
                    'report' => [
                        [
                            'name' => 'test_report_1',
                            'connection' => 'sales',
                            'source' => [
                                [
                                    'name' => 'sales_order',
                                    'alias' => 'orders',
                                    'attribute' => [
                                        [
                                            'name' => 'entity_id',
                                            'alias' => 'identifier',
                                        ]
                                    ],
                                    'filter' => [
                                        [
                                            'glue' => 'and',
                                            'condition' => [
                                                [
                                                    'attribute' => 'entity_id',
                                                    'operator' => 'gt',
                                                    '_value' => '10'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'test_report_2',
                            'connection' => 'default',
                            'source' => [
                                [
                                    'name' => 'customer_entity',
                                    'alias' => 'customers',
                                    'attribute' => [
                                        [
                                            'name' => 'email'
                                        ]
                                    ],
                                    'filter' => [
                                        [
                                            'glue' => 'and',
                                            'condition' => [
                                                [
                                                    'attribute' => 'dob',
                                                    'operator' => 'null'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $dom->loadXML(file_get_contents(__DIR__ . '/../_files/valid_reports.xml'));

        $this->assertEquals($expectedArray, $this->subject->convert($dom));
    }
}
