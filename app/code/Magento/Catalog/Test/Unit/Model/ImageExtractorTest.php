<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\ImageExtractor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ImageExtractorTest extends TestCase
{
    /**
     * @var ImageExtractor
     */
    private $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(ImageExtractor::class);
    }

    public function testProcess()
    {
        $expectedArray = include __DIR__ . '/_files/converted_view.php';
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
