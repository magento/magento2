<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\DomDocument;

use DOMDocument;
use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DomDocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var DOMDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private $domDocumentMock;

    /**
     * @var DomDocumentFactory
     */
    private $domDocumentFactory;

    /**
     * @var string
     */
    private $xmlSample = <<<EOT
<?xml version="1.0"?>
<root>
</root>
EOT;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->domDocumentMock = $this->createMock(DOMDocument::class);
        $this->domDocumentFactory = $this->objectManagerHelper->getObject(
            DomDocumentFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate($data = null)
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(DOMDocument::class)
            ->willReturn($this->domDocumentMock);

        if (empty($data) || !is_string($data)) {
            $this->domDocumentMock->expects($this->never())
                ->method('loadXML');
        } else {
            $this->domDocumentMock->expects($this->once())
                ->method('loadXML')
                ->with($data)
                ->willReturn(true);
        }

        $this->domDocumentFactory->create($data);
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            [null],
            [''],
            [$this->xmlSample]
        ];
    }
}
