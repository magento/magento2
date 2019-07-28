<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ImageExtractorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ImageExtractor
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(\Magento\Catalog\Model\ImageExtractor::class);
    }

    public function testProcess()
    {
        $expectedArray = include(__DIR__ . '/_files/converted_view.php');
        $this->assertSame($expectedArray, $this->model->process($this->getDomElement(), 'media'));
    }

    /**
     * Get mocked dom element
     *
     * @return \DOMElement
     */
    private function getDomElement()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/_files/valid_view.xml');
        return $doc->getElementsByTagName('images')->item(0);
    }
}
