<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config;

/**
 * Class ResolutionRulesTest
 *
 * Test for class \Magento\Paypal\Block\Adminhtml\System\Config\ResolutionRules
 */
class ResolutionRulesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Block\Adminhtml\System\Config\ResolutionRules
     */
    protected $resolutionRules;

    /** @var  \Magento\Backend\Block\Template\Context */
    protected $context;

    /**
     * @var \Magento\Paypal\Model\Config\Rules\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->context = $objectManager->getObject('\Magento\Backend\Block\Template\Context');

        $this->readerMock = $this->getMockBuilder('Magento\Paypal\Model\Config\Rules\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolutionRules = new \Magento\Paypal\Block\Adminhtml\System\Config\ResolutionRules(
            $this->context,
            $this->readerMock
        );
    }

    /**
     * Run test for getJson method
     *
     * @return void
     */
    public function testGetJson()
    {
        $expected = ['test' => 'test-value'];

        $this->readerMock->expects($this->once())
            ->method('read')
            ->willReturn($expected);

        $this->assertEquals(json_encode($expected), $this->resolutionRules->getJson());
    }
}
