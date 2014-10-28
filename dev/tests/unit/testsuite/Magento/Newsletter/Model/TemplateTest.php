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
namespace Magento\Newsletter\Model;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $isSingleStore
     * @dataProvider getProcessedTemplateDataProvider
     */
    public function testGetProcessedTemplate($isSingleStore)
    {
        $design = $this->getMock('Magento\Framework\View\DesignInterface');
        $context = $this->getMock('Magento\Framework\Model\Context', array(), array(), '', false);
        $registry = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);

        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $storeManager->expects($this->once())->method('hasSingleStore')->will($this->returnValue($isSingleStore));

        $request = $this->getMock('Magento\Framework\App\RequestInterface', array(), array(), '', false);

        if ($isSingleStore) {
            $store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
            $store->expects($this->once())->method('getId')->will($this->returnValue('test_id'));

            $storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));
        } else {
            $request->expects(
                $this->once()
            )->method(
                'getParam'
            )->with(
                'store_id'
            )->will(
                $this->returnValue('test_id')
            );
        }

        $filter = $this->getMock('Magento\Newsletter\Model\Template\Filter', array(), array(), '', false);
        $appEmulation = $this->getMock('Magento\Core\Model\App\Emulation', array(), array(), '', false);
        $filter->expects($this->once())->method('setStoreId')->with('test_id');
        $filter->expects($this->once())->method('setIncludeProcessor')->will($this->returnSelf());
        $filter->expects(
            $this->once()
        )->method(
            'filter'
        )->with(
            'template text'
        )->will(
            $this->returnValue('processed text')
        );

        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $templateFactory = $this->getMock('Magento\Newsletter\Model\TemplateFactory');
        $data = array('template_text' => 'template text');

        $filterManager = $this->getMock('\Magento\Framework\Filter\FilterManager', array(), array(), '', false);

        /** @var \Magento\Newsletter\Model\Template $model */
        $model = $this->getMock(
            'Magento\Newsletter\Model\Template',
            array('_init'),
            array(
                $context,
                $design,
                $registry,
                $appEmulation,
                $storeManager,
                $request,
                $filter,
                $scopeConfig,
                $templateFactory,
                $filterManager,
                $data
            )
        );

        $result = $model->getProcessedTemplate();
        $this->assertEquals('processed text', $result);
    }

    /**
     * @return array
     */
    public static function getProcessedTemplateDataProvider()
    {
        return array('single store' => array(true), 'multi store' => array(false));
    }
}
