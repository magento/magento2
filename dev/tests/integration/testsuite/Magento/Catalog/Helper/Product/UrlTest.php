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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Helper\Product;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Url
     */
    protected $_helper;

    public static function setUpBeforeClass()
    {
        /** @var $configModel \Magento\Core\Model\Config */
        $configModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Config');
        // @todo re-implement this test
        $data = array(
            'from' => '™',
            'to' => 'TM',
        );
        $configModel->setValue('url/convert/char8482', $data);
    }

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Helper\Product\Url');
    }

    public function testGetConvertTable()
    {
        $convertTable = $this->_helper->getConvertTable();
        $this->assertInternalType('array', $convertTable);
        $this->assertNotEmpty($convertTable);
    }

    public function testFormat()
    {
        $this->assertEquals('TM', $this->_helper->format('™'));
    }
}
