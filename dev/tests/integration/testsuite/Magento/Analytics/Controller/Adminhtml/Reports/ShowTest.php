<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Controller\Adminhtml\Reports;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class ShowTest extends AbstractBackendController
{
    private const REPORT_HOST = 'docs.magento.com';
    /**
     * @inheritDoc
     */
    protected $resource = 'Magento_Analytics::advanced_reporting';
    /**
     * @inheritDoc
     */
    protected $uri = 'backend/analytics/reports/show';
    /**
     * @inheritDoc
     */
    public function testAclHasAccess()
    {
        parent::testAclHasAccess();
        $this->assertSame(302, $this->getResponse()->getHttpResponseCode());
        $this->assertSame(self::REPORT_HOST, $this->getResponse()->getHeader('location')->uri()->getHost());
    }
}
