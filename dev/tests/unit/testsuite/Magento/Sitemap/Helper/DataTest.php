<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sitemap\Helper;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

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
