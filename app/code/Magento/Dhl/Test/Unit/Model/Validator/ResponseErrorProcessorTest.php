<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Dhl\Test\Unit\Model\Validator;

use Magento\Dhl\Model\Validator\ResponseErrorProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Shipping\Model\Simplexml\Element;
use PHPUnit\Framework\TestCase;

class ResponseErrorProcessorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ResponseErrorProcessor
     */
    private $responseErrorProcessor;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->responseErrorProcessor = $this->objectManager->getObject(ResponseErrorProcessor::class);
    }

    /**
     * Test process outputs correctly formatted error messages
     *
     * @param array $data
     * @dataProvider processProvider
     */
    public function testProcess($data)
    {
        $xml = $this->getXml($data['file']);

        $result = $this->responseErrorProcessor->process($xml, $data['isShippingLabel']);

        $this->assertEquals($data['errorMessage'], $result->render());
        $this->assertNotNull($result->getArguments()[0]);
    }

    /**
     * Retrieve SimpleXmlElement from XML file
     *
     * @param string $file
     * @return \SimpleXMLElement
     */
    private function getXml($file)
    {
        $rawXml = file_get_contents(__DIR__ . '/_files/' . $file);
        return simplexml_load_string($rawXml, Element::class);
    }

    /**
     * @return array
     */
    public static function processProvider()
    {
        return [
            [
                [
                    'file' => 'invalidDHLResponse.xml',
                    'errorMessage' => 'Error #111 : Error in parsing request XML',
                    'isShippingLabel' => false,
                ],
            ],
            [
                [
                    'file' => 'invalidDHLResponseForShippingLabel.xml',
                    'errorMessage' => 'Error #123 : Error in shipping request XML',
                    'isShippingLabel' => true,
                ],
            ],
            [
                [
                    'file' => 'invalidDHLResponseForQuoteResponse.xml',
                    'errorMessage' => 'Error #321 : Error in quote request XML',
                    'isShippingLabel' => false,
                ],
            ],
        ];
    }
}
