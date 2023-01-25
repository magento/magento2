<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Msrp\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\Msrp\Model\Config as MsrpConfig;
use Magento\Msrp\Ui\DataProvider\Product\Form\Modifier\Msrp;
use PHPUnit\Framework\MockObject\MockObject;

class MsrpTest extends AbstractModifierTest
{
    /**
     * @var MsrpConfig|MockObject
     */
    private $msrpConfigMock;

    protected function setUp(): void
    {
        $this->msrpConfigMock = $this->getMockBuilder(MsrpConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Msrp::class, [
            'locator' => $this->locatorMock,
            'msrpConfig' => $this->msrpConfigMock,
        ]);
    }

    public function testModifyData()
    {
        $this->assertSame([], $this->getModel()->modifyData([]));
    }

    public function testModifyMeta()
    {
        $this->assertSame([], $this->getModel()->modifyMeta([]));
    }
}
