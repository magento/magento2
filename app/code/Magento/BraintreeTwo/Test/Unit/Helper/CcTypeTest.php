<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Helper;

use Magento\BraintreeTwo\Helper\CcType;
use Magento\BraintreeTwo\Model\Adminhtml\Source\CcType as CcTypeSource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CcTypeTest
 */
class CcTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\BraintreeTwo\Helper\CcType
     */
    private $helper;

    /** @var \Magento\BraintreeTwo\Model\Adminhtml\Source\CcType|\PHPUnit_Framework_MockObject_MockObject */
    private $ccTypeSource;

    protected function setUp()
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
     * @covers \Magento\BraintreeTwo\Helper\CcType::getCcTypes
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
