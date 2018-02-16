<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Helper;

use \Magento\Sitemap\Helper\Data;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sitemap\Helper\Data */
    protected $data;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Magento\Sitemap\Helper\Data';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->scopeConfig = $context->getScopeConfig();
        $this->data = $objectManagerHelper->getObject($className, $arguments);
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
