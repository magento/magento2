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
 * @package     Magento_Downloadable
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable_Links
     */
    protected $_block;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $urlBuilder = $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false);
        $attributeFactory = $this->getMock('Magento\Eav\Model\Entity\AttributeFactory', array(), array(), '', false);
        $urlFactory = $this->getMock('Magento\Backend\Model\UrlFactory', array(), array(), '', false);

        $this->_block = $objectManagerHelper->getObject(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links',
            array(
                'urlBuilder' => $urlBuilder,
                'Magento\Eav\Model\Entity\AttributeFactory' => $attributeFactory,
                'Magento\Backend\Model\UrlFactory' => $urlFactory
            )
        );
    }

    /**
     * Test that getConfig method retrieve \Magento\Object object
     */
    public function testGetConfig()
    {
        $this->assertInstanceOf('Magento\Object', $this->_block->getConfig());
    }
}
