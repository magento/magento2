<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $context = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);

        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManager->expects($this->once())->method('hasSingleStore')->will($this->returnValue($isSingleStore));

        $request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);

        if ($isSingleStore) {
            $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
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

        $filter = $this->getMock('Magento\Newsletter\Model\Template\Filter', [], [], '', false);
        $appEmulation = $this->getMock('Magento\Core\Model\App\Emulation', [], [], '', false);
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
        $templateFactory = $this->getMock('Magento\Newsletter\Model\TemplateFactory', [], [], '', false);
        $data = ['template_text' => 'template text'];

        $filterManager = $this->getMock('\Magento\Framework\Filter\FilterManager', [], [], '', false);

        /** @var \Magento\Newsletter\Model\Template $model */
        $model = $this->getMock(
            'Magento\Newsletter\Model\Template',
            ['_init'],
            [
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
            ]
        );

        $result = $model->getProcessedTemplate();
        $this->assertEquals('processed text', $result);
    }

    /**
     * @return array
     */
    public static function getProcessedTemplateDataProvider()
    {
        return ['single store' => [true], 'multi store' => [false]];
    }
}
