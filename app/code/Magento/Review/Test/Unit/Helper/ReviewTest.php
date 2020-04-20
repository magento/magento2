<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Review\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Review\Helper\Review as HelperReview;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class \Magento\Review\Test\Unit\Helper\ReviewTest
 */
class ReviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var HelperReview
     */
    private $helper;

    /**
     * @var MockObject|Context
     */
    private $context;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Setup environment
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);

        $this->objectManager = new ObjectManagerHelper($this);
        $this->helper = $this->objectManager->getObject(
            HelperReview::class,
            [
                'context' => $this->context
            ]
        );
    }

    /**
     * Test isEnableReview() function
     */
    public function testIsEnableReview()
    {
        $this->scopeConfig->expects($this->any())->method('isSetFlag')
            ->with(HelperReview::XML_PATH_REVIEW_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn('1');

        $this->assertEquals(true, $this->helper->isEnableReview());
    }

    /**
     * Test getDefaultNoRouteUrl() function
     */
    public function testGetDefaultNoRouteUrl()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')
            ->with(HelperReview::XML_PATH_DEFAULT_NO_ROUTE_URL, ScopeInterface::SCOPE_STORE)
            ->willReturn('admin/noroute/index');

        $this->assertEquals('admin/noroute/index', $this->helper->getDefaultNoRouteUrl());
    }
}
