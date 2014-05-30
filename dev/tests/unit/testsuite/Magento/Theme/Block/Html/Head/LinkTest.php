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

namespace Magento\Theme\Block\Html\Head;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Head\Link
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_assetRepo;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $context = $this->getMock('\Magento\Framework\View\Element\Template\Context', array(), array(), '', false);
        $this->_assetRepo = $this->getMock('\Magento\Framework\View\Asset\Repository', array(), array(), '', false);

        $context->expects($this->once())
            ->method('getAssetRepository')
            ->will($this->returnValue($this->_assetRepo));

        $this->_block = $objectManagerHelper->getObject(
            '\Magento\Theme\Block\Html\Head\Link',
            array('context' => $context)
        );

        $this->_block->setData('url', 'urlValue');
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Magento\Framework\View\Element\Template', $this->_block);
    }

    public function testGetAsset()
    {
        $asset = $this->getMock('\Magento\Framework\View\Asset\Remote', array(), array(), '', false);

        $this->_assetRepo->expects($this->once())
            ->method('createRemoteAsset')
            ->with('urlValue', 'link')
            ->will($this->returnValue($asset));

        $this->assertSame($this->_block->getAsset(), $asset);
    }
}
