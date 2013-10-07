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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Page\Block\Link;

class CurrentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGetUrl()
    {
        $path = 'test/path';
        $url = 'http://example.com/asdasd';

        /** @var  \Magento\Core\Block\Template\Context $context */
        $context = $this->_objectManagerHelper->getObject('Magento\Core\Block\Template\Context');
        $urlBuilder = $context->getUrlBuilder();
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));

        $link = $this->_objectManagerHelper->getObject(
            'Magento\Page\Block\Link\Current',
            array(
                'context' => $context,
            )
        );
        $link->setPath($path);
        $this->assertEquals($url, $link->getHref());
    }


    public function testIsCurrentIfIsset()
    {
        $link = $this->_objectManagerHelper->getObject('Magento\Page\Block\Link\Current');
        $link->setCurrent(true);
        $this->assertTrue($link->IsCurrent());
    }

    public function testIsCurrent()
    {
        $path = 'test/path';
        $url = 'http://example.com/a/b';

        /** @var  \Magento\Core\Block\Template\Context $context */
        $context = $this->_objectManagerHelper->getObject('Magento\Core\Block\Template\Context');

        $request = $context->getRequest();
        $request->expects($this->once())->method('getModuleName')->will($this->returnValue('a'));
        $request->expects($this->once())->method('getControllerName')->will($this->returnValue('b'));
        $request->expects($this->once())->method('getActionName')->will($this->returnValue('d'));

        $context->getFrontController()->expects($this->once())->method('getDefault')
            ->will($this->returnValue(array('action' => 'd')));

        $urlBuilder = $context->getUrlBuilder();
        $urlBuilder->expects($this->at(0))->method('getUrl')->with($path)->will($this->returnValue($url));
        $urlBuilder->expects($this->at(1))->method('getUrl')->with('a/b')->will(
            $this->returnValue($url)
        );

        /** @var \Magento\Page\Block\Link\Current $link */
        $link = $this->_objectManagerHelper->getObject(
            'Magento\Page\Block\Link\Current',
            array(
                'context' => $context,
            )
        );
        $link->setPath($path);
        $this->assertTrue($link->isCurrent());
    }

    public function testIsCurrentFalse()
    {
        /** @var  \Magento\Core\Block\Template\Context $context */
        $context = $this->_objectManagerHelper->getObject('Magento\Core\Block\Template\Context');

        $urlBuilder = $context->getUrlBuilder();
        $urlBuilder->expects($this->at(0))->method('getUrl')->will($this->returnValue('1'));
        $urlBuilder->expects($this->at(1))->method('getUrl')->will($this->returnValue('2'));

        /** @var \Magento\Page\Block\Link\Current $link */
        $link = $this->_objectManagerHelper->getObject(
            'Magento\Page\Block\Link\Current',
            array(
                'context' => $context,
            )
        );
        $this->assertFalse($link->isCurrent());
    }
}
