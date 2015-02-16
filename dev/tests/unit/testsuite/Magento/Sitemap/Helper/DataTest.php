<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\ObjectManager;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sitemap\Helper\Data */
    protected $data;

    /** @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    protected function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->data = (new ObjectManager($this))->getObject(
            'Magento\Sitemap\Helper\Data',
            [
                'context' => $this->context,
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    public function testGetValidPaths()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')->will($this->returnValueMap(
            [
                [Data::XML_PATH_SITEMAP_VALID_PATHS, ScopeInterface::SCOPE_STORE, null, ['path1']],
                [Data::XML_PATH_PUBLIC_FILES_VALID_PATHS, ScopeInterface::SCOPE_STORE, null, ['path2']],
            ]
        ));

        $this->assertEquals(['path1', 'path2'], $this->data->getValidPaths());
    }
}
