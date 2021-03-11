<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Block\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Block\Adminhtml\CaseInfo;
use Magento\Signifyd\Model\CaseManagement;
use Magento\Signifyd\Model\Config;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Tests for Signifyd block information.
 *
 * Class CaseInfoTest
 */
class CaseInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaseInterface|MockObject
     */
    private $caseEntity;

    /**
     * @var CaseInfo
     */
    private $caseInfo;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var CaseManagement|MockObject
     */
    private $caseManagement;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects(self::once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->caseManagement = $this->getMockBuilder(CaseManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScore'])
            ->getMockForAbstractClass();

        $this->caseInfo = $objectManager->getObject(CaseInfo::class, [
            'context' => $this->context,
            'config' => $this->config,
            'caseManagement' => $this->caseManagement
        ]);
    }

    /**
     * Checks label according to Signifyd Guarantee Disposition.
     *
     * @param string $guaranteeDisposition
     * @param string $expectedLabel
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getCaseGuaranteeDisposition()
     * @dataProvider getGuaranteeLabelDataProvider
     */
    public function testGetGuaranteeDisposition($guaranteeDisposition, $expectedLabel)
    {
        $this->caseManagement->expects(self::once())
            ->method('getByOrderId')
            ->willReturn($this->caseEntity);

        $this->caseEntity->expects(self::atLeastOnce())
            ->method('getGuaranteeDisposition')
            ->willReturn($guaranteeDisposition);

        self::assertEquals(
            $expectedLabel,
            $this->caseInfo->getCaseGuaranteeDisposition()
        );
    }

    /**
     * Case Guarantee Disposition and corresponding label data provider.
     *
     * @return array
     */
    public function getGuaranteeLabelDataProvider()
    {
        return [
            [CaseInterface::GUARANTEE_APPROVED, __('Approved')],
            [CaseInterface::GUARANTEE_DECLINED, __('Declined')],
            [CaseInterface::GUARANTEE_PENDING, __('Pending')],
            [CaseInterface::GUARANTEE_CANCELED, __('Canceled')],
            [CaseInterface::GUARANTEE_IN_REVIEW, __('In Review')],
            [CaseInterface::GUARANTEE_UNREQUESTED, __('Unrequested')],
            ['Unregistered', '']
        ];
    }

    /**
     * Checks case property getter with empty case.
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getCaseProperty
     */
    public function testCasePropertyWithEmptyCase()
    {
        $this->caseManagement->expects(self::once())
            ->method('getByOrderId')
            ->willReturn(null);

        self::assertEquals(
            '',
            $this->caseInfo->getCaseGuaranteeDisposition()
        );
    }
}
