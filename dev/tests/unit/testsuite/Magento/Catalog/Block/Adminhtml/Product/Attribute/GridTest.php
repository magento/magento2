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
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute;

class GridTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRowUrl()
    {
        $attribute = $this->getMock('Magento\Catalog\Model\Resource\Eav\Attribute', array(), array(), '', false);
        $attribute->expects($this->once())->method('getAttributeId')->will($this->returnValue(2));

        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);

        $urlBuilder = $this->getMock('Magento\Framework\UrlInterface', array(), array(), '', false);
        $urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('catalog/*/edit'),
            $this->equalTo(array('attribute_id' => 2))
        )->will(
            $this->returnValue('catalog/product_attribute/edit/id/2')
        );

        $context = $this->getMock('Magento\Backend\Block\Template\Context', array(), array(), '', false);
        $context->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));
        $context->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $data = array('context' => $context);

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid $block */
        $block = $helper->getObject('Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid', $data);

        $this->assertEquals('catalog/product_attribute/edit/id/2', $block->getRowUrl($attribute));
    }
}
