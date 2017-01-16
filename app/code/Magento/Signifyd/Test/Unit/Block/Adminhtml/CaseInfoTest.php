<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Block\Adminhtml;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\CaseManagement;
use Magento\Signifyd\Model\Guarantee\CreateGuaranteeAbility;
use Magento\Signifyd\Block\Adminhtml\CaseInfo;

/**
 * Tests for Signifyd block information.
 *
 * Class CaseInfoTest
 */
class CaseInfoTest extends \PHPUnit_Framework_TestCase
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
     * @var Context
     */
    private $context;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * @var CreateGuaranteeAbility
     */
    private $createGuaranteeAbility;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp()
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

        $this->createGuaranteeAbility = $this->getMockBuilder(CreateGuaranteeAbility::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScore'])
            ->getMockForAbstractClass();

        $this->caseInfo = $objectManager->getObject(CaseInfo::class, [
            'context' => $this->context,
            'config' => $this->config,
            'caseManagement' => $this->caseManagement,
            'createGuaranteeAbility' => $this->createGuaranteeAbility
        ]);
    }

    /**
     * Checks css class according to case entity score value.
     *
     * @param integer $score
     * @param string $expectedClassName
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getScoreClass
     * @dataProvider getScoreClassDataProvider
     */
    public function testGetScoreClass($score, $expectedClassName)
    {
        $this->caseEntity->expects($this->once())
            ->method('getScore')
            ->willReturn($score);

        $this->caseManagement->expects(self::once())
            ->method('getByOrderId')
            ->willReturn($this->caseEntity);

        self::assertEquals(
            $expectedClassName,
            $this->caseInfo->getScoreClass()
        );
    }

    /**
     * Checks case property getter with real case.
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getCaseProperty
     */
    public function testCasePropertyWithCaseExists()
    {
        $score = 575;

        $this->caseEntity->expects($this->once())
            ->method('getScore')
            ->willReturn($score);

        $this->caseManagement->expects(self::once())
            ->method('getByOrderId')
            ->willReturn($this->caseEntity);

        self::assertEquals(
            $score,
            $this->caseInfo->getCaseScore()
        );
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
            0,
            $this->caseInfo->getCaseScore()
        );
    }

    /**
     * Case scores and corresponding class name data provider

     * @return array
     */
    public function getScoreClassDataProvider()
    {
        return [
            [300, 'red'],
            [400, 'yellow'],
            [500, 'green'],
        ];
    }
}
