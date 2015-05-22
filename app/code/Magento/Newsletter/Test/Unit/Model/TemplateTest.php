<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $design;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Store\Model\App\Emulation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Email\Model\Template\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailConfig;

    /**
     * @var \Magento\Newsletter\Model\TemplateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterManager;

    /**
     * @var \Magento\Newsletter\Model\Template\FilterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterFactory;

    public function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->design = $this->getMockBuilder('Magento\Framework\View\DesignInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->appEmulation = $this->getMockBuilder('Magento\Store\Model\App\Emulation')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepo = $this->getMockBuilder('Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailConfig = $this->getMockBuilder('Magento\Email\Model\Template\Config')
            ->disableOriginalConstructor()
            ->getMock();
        // TODO: Remove the unneeded ones
        $this->templateFactory = $this->getMockBuilder('Magento\Newsletter\Model\TemplateFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterManager = $this->getMockBuilder('Magento\Framework\Filter\FilterManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterFactory = $this->getMockBuilder('Magento\Newsletter\Model\Template\FilterFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Return the model under test with additional methods mocked.
     *
     * @param $mockedMethods array
     * @return \Magento\Newsletter\Model\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModelMock(array $mockedMethods = [])
    {
        return $this->getMockBuilder('Magento\Newsletter\Model\Template')
            ->setMethods(array_merge($mockedMethods, ['__wakeup', '__sleep', '_init']))
            ->setConstructorArgs(
                [
                    $this->context,
                    $this->design,
                    $this->registry,
                    $this->appEmulation,
                    $this->storeManager,
                    $this->request,
                    $this->scopeConfig,
                    $this->assetRepo,
                    $this->filesystem,
                    $this->objectManager,
                    $this->emailConfig,
                    $this->templateFactory,
                    $this->filterManager,
                    $this->filterFactory
                ]
            )
            ->getMock();
    }

    /**
     * @param bool $isSingleStore
     * @param $variables array
     * @param $templateType string
     * @param $storeId int
     * @param $expectedVariables array
     * @param $expectedResult string
     * @dataProvider getProcessedTemplateProvider
     */
    public function testGetProcessedTemplate($isSingleStore, $variables, $templateType, $storeId, $expectedVariables, $expectedResult)
    {
        $this->storeManager->expects($this->once())
            ->method('hasSingleStore')
            ->will($this->returnValue($isSingleStore));

        if ($isSingleStore) {

        } else {
            $this->request->expects($this->once())
                ->method('getParam')
                ->with('store_id')
                ->will($this->returnValue($storeId));
        }

        $data = ['template_text' => 'template text'];

        /** @var \Magento\Newsletter\Model\Template $model */
        $model = $this->getMock(
            'Magento\Newsletter\Model\Template',
            ['_init'],
            [
                $this->context,
                $this->design,
                $this->registry,
                $this->appEmulation,
                $this->storeManager,
                $this->request,
                $this->scopeConfig,
                $this->assetRepo,
                $this->filesystem,
                $this->objectManager,
                $this->emailConfig,
                $this->templateFactory,
                $this->filterManager,
                $this->filterFactory,
                $data
            ]
        );

        $filterTemplate = $this->getMockBuilder('Magento\Newsletter\Model\Template\Filter')
            ->setMethods([
                'setUseSessionInUrl',
                'setPlainTemplateMode',
                'setIsChildTemplate',
                'setTemplateModel',
                'setVariables',
                'setStoreId',
                'filter',
                'getStoreId',
                'getInlineCssFiles',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $filterTemplate->expects($this->once())
            ->method('setUseSessionInUrl')
            ->with(false)
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setPlainTemplateMode')
            ->with($templateType === \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT)
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setIsChildTemplate')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setTemplateModel')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setVariables')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));

        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getFrontendName', 'getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontendName'));
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        //TODO - is this the right way? the store is not setup until runtime and
        // is returned from getProcessedTemplate
        $expectedVariables['store'] = $store;

        $model = $this->getModelMock(['getDesignConfig', '_applyDesignConfig', 'getPreparedTemplateText', 'getTemplateText']);
        $filterTemplate->expects($this->any())
            ->method('setVariables')
            ->with(array_merge([ 'this' => $model], $expectedVariables));

        $model->setTemplateFilter($filterTemplate);
        $model->setTemplateType($templateType);

        $designConfig = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeId = 'storeId';
        $designConfig->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeId));
        $model->expects($this->once())
            ->method('getDesignConfig')
            ->will($this->returnValue($designConfig));

        $preparedTemplateText = $expectedResult; //'prepared text';
        $model->expects($this->once())
            ->method('getPreparedTemplateText')
            ->will($this->returnValue($preparedTemplateText));
        $model->expects($this->once())
            ->method('getTemplateText')
            ->will($this->returnValue($preparedTemplateText));
        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($preparedTemplateText)
            ->will($this->returnValue($expectedResult));

        $result = $model->getProcessedTemplate($variables);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getProcessedTemplateProvider()
    {
        return [
            'default' => [
                'isSingleStore' => true,
                'variables' => [],
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT,
                'storeId' => 1,
                'expectedVariables' => [
                    'logo_url' => null,
                    'logo_alt' => 'frontendName',
                    'store' => null,
                    'logo_width' => null,
                    'logo_height' => null,
                    'store_phone' => null,
                    'store_hours' => null,
                    'store_email' => null,
                ],
                'expectedResult' => 'expected result',
            ],
            'logo variables set' => [
                'isSingleStore' => false,
                'variables' => [
                    'logo_url' => 'http://example.com/logo',
                    'logo_alt' => 'Logo Alt',
                ],
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML,
                'storeId' => 1,
                'expectedVariables' => [
                    'logo_url' => 'http://example.com/logo',
                    'logo_alt' => 'Logo Alt',
                    'store' => null,
                    'logo_width' => null,
                    'logo_height' => null,
                    'store_phone' => null,
                    'store_hours' => null,
                    'store_email' => null,
                    'template_styles' => null,
                ],
                'expectedResult' => 'expected result',
            ],
        ];
    }
}
