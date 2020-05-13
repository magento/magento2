<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Helper;

use Magento\Braintree\Helper\CcType;
use Magento\Braintree\Model\Adminhtml\Source\CcType as CcTypeSource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CcTypeTest extends TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Braintree\Helper\CcType
     */
    private $helper;

    /** @var \Magento\Braintree\Model\Adminhtml\Source\CcType|MockObject */
    private $ccTypeSource;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->ccTypeSource = $this->getMockBuilder(CcTypeSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['toOptionArray'])
            ->getMock();

        $this->helper = $this->objectManager->getObject(CcType::class, [
            'ccTypeSource' => $this->ccTypeSource
        ]);
    }

    /**
     * @covers \Magento\Braintree\Helper\CcType::getCcTypes
     */
    public function testGetCcTypes()
    {
        $this->ccTypeSource->expects(static::once())
            ->method('toOptionArray')
            ->willReturn([
                'label' => 'VISA', 'value' => 'VI'
            ]);

        $this->helper->getCcTypes();

        $this->ccTypeSource->expects(static::never())
            ->method('toOptionArray');

        $this->helper->getCcTypes();
    }
}
