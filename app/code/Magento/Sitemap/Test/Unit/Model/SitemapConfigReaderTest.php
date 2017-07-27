<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\SitemapConfigReader;
use Magento\Store\Model\ScopeInterface;

class SitemapConfigReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValidPaths()
    {
        $scopeConfigMock = $this->getScopeConfigMock();

        $configReader = new SitemapConfigReader($scopeConfigMock);

        $this->assertEquals(['path1', 'path2'], $configReader->getValidPaths());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getScopeConfigMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                [SitemapConfigReader::XML_PATH_SITEMAP_VALID_PATHS, ScopeInterface::SCOPE_STORE, null, ['path1']],
                [SitemapConfigReader::XML_PATH_PUBLIC_FILES_VALID_PATHS, ScopeInterface::SCOPE_STORE, null, ['path2']],
            ]);

        return $scopeConfigMock;
    }
}
