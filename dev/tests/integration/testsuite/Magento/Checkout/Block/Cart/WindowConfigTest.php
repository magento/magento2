<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Block\Cart;

use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
#[DbIsolation(false)]
class WindowConfigTest extends TestCase
{
    public function testStoreScopeConfigIsRenderedWhenMinicartIsRemoved(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(DesignInterface::class)->setDefaultDesignTheme();
        $page = $objectManager->get(PageFactory::class)->create();
        $page->addDefaultHandle();
        $page->getLayout()->getUpdate()->addUpdate('<referenceBlock name="minicart" remove="true"/>');
        $response = $objectManager->create(HttpResponse::class);
        $page->renderResult($response);
        $html = $response->getBody();

        $this->assertStringNotContainsString('data-block=\'minicart\'', $html);
        $this->assertStringNotContainsString('data-block="minicart"', $html);
        $this->assertStringContainsString('window.checkout = ', $html);
        $this->assertStringContainsString('"websiteId"', $html);
        $this->assertStringContainsString('"storeId"', $html);
    }
}
