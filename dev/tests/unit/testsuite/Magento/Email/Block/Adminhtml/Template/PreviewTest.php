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
namespace Magento\Email\Block\Adminhtml\Template;

class PreviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    const MALICIOUS_TEXT = 'test malicious';

    /**
     * Init data
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

    }

    /**
     * Check of processing email templates
     *
     * @param array $requestParamMap
     *
     * @dataProvider toHtmlDataProvider
     * @param $requestParamMap
     */
    public function testToHtml($requestParamMap)
    {
        $template = $this->getMock('Magento\Email\Model\Template',
            array('setDesignConfig', 'getDesignConfig', '__wakeup', 'getProcessedTemplate'), array(), '', false);
        $template->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($this->equalTo(array()), $this->equalTo(true))
            ->will($this->returnValue(self::MALICIOUS_TEXT));
        $emailFactory = $this->getMock('Magento\Email\Model\TemplateFactory', array('create'), array(), '', false);
        $emailFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(array('data' => array('area' => \Magento\Framework\App\Area::AREA_FRONTEND))))
            ->will($this->returnValue($template));


        $request = $this->getMock('Magento\Framework\App\RequestInterface');
        $request->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));
        $eventManage = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $design = $this->getMock('Magento\Framework\View\DesignInterface');
        $store = $this->getMock('Magento\Store\Model\Store', array('getId', '__wakeup'), array(), '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $storeManager = $this->getMock('\Magento\Framework\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getDefaultStoreView')->will($this->returnValue(null));
        $storeManager->expects($this->any())->method('getStores')->will($this->returnValue([$store]));

        $context = $this->getMock('Magento\Backend\Block\Template\Context',
            array('getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage', 'getStoreManager'),
            array(), '', false
        );
        $context->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManage));
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $context->expects($this->any())->method('getDesignPackage')->will($this->returnValue($design));
        $context->expects($this->any())->method('getStoreManager')->will($this->returnValue($storeManager));

        $maliciousCode = $this->getMock(
            'Magento\Framework\Filter\Input\MaliciousCode',
            array('filter'),
            array(),
            '',
            false
        );
        $maliciousCode->expects($this->once())->method('filter')->with($this->equalTo($requestParamMap[1][2]))
            ->will($this->returnValue(self::MALICIOUS_TEXT));

        $preview = $this->objectManagerHelper->getObject(
            'Magento\Email\Block\Adminhtml\Template\Preview',
            array(
                'context' => $context,
                'emailFactory' => $emailFactory,
                'maliciousCode' => $maliciousCode
            )
        );
        $this->assertEquals(self::MALICIOUS_TEXT, $preview->toHtml());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function toHtmlDataProvider()
    {
        return array(
            array('data 1' => array(
                array('type', null, ''),
                array('text', null, sprintf('<javascript>%s</javascript>', self::MALICIOUS_TEXT)),
                array('styles', null, '')
            )),
            array('data 2' => array(
                array('type', null, ''),
                array('text', null, sprintf('<iframe>%s</iframe>', self::MALICIOUS_TEXT)),
                array('styles', null, '')
            )),
            array('data 3' => array(
                array('type', null, ''),
                array('text', null, self::MALICIOUS_TEXT),
                array('styles', null, '')
            )),
        );
    }

    /**
     * Test exception with no store found
     *
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Design config must have area and store.
     */
    public function testToHtmlWithException()
    {
        $template = $this->getMock('Magento\Email\Model\Template',
            array('__wakeup', 'load'), array(), '', false);
        $template->expects($this->once())
            ->method('load')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());
        $emailFactory = $this->getMock('Magento\Email\Model\TemplateFactory', array('create'), array(), '', false);
        $emailFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(array('data' => array('area' => \Magento\Framework\App\Area::AREA_FRONTEND))))
            ->will($this->returnValue($template));


        $request = $this->getMock('Magento\Framework\App\RequestInterface');
        $request->expects($this->any())->method('getParam')->with($this->equalTo('id'))->will($this->returnValue(1));
        $eventManage = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $design = $this->getMock('Magento\Framework\View\DesignInterface');
        $storeManager = $this->getMock('\Magento\Framework\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getDefaultStoreView')->will($this->returnValue(null));
        $storeManager->expects($this->any())->method('getStores')->will($this->returnValue([]));

        $context = $this->getMock('Magento\Backend\Block\Template\Context',
            array('getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage', 'getStoreManager'),
            array(), '', false
        );
        $context->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManage));
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $context->expects($this->any())->method('getDesignPackage')->will($this->returnValue($design));
        $context->expects($this->any())->method('getStoreManager')->will($this->returnValue($storeManager));

        $maliciousCode = $this->getMock(
            'Magento\Framework\Filter\Input\MaliciousCode',
            array('filter'),
            array(),
            '',
            false
        );
        $maliciousCode->expects($this->once())->method('filter')
            ->will($this->returnValue(''));

        $preview = $this->objectManagerHelper->getObject(
            'Magento\Email\Block\Adminhtml\Template\Preview',
            array(
                'context' => $context,
                'emailFactory' => $emailFactory,
                'maliciousCode' => $maliciousCode
            )
        );
        $preview->toHtml();
    }
}
