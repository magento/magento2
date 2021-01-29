<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\Adminhtml\System\Config\ResolutionRules;
use Magento\Paypal\Model\Config\Rules\Reader;

/**
 * Class ResolutionRulesTest
 *
 * Test for class \Magento\Paypal\Block\Adminhtml\System\Config\ResolutionRules
 */
class ResolutionRulesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResolutionRules
     */
    protected $resolutionRules;

    /** @var  Context */
    protected $context;

    /**
     * @var Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $readerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->context = $objectManager->getObject(Context::class);

        $this->readerMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolutionRules = new ResolutionRules(
            $this->context,
            $this->readerMock
        );
    }

    /**
     * Run test for getJson method
     *
     * @param array $incoming
     * @param string $outgoing
     * @dataProvider getJsonDataProvider
     */
    public function testGetJson($incoming, $outgoing)
    {
        $this->readerMock->expects($this->once())
            ->method('read')
            ->willReturn($incoming);

        $this->assertEquals(
            $outgoing,
            $this->resolutionRules->getJson()
        );
    }

    /**
     * @return array
     */
    public function getJsonDataProvider()
    {
        return [
            [['test' => 'test-value'], '{"test":"test-value"}'],
            [[], '{}']
        ];
    }
}
