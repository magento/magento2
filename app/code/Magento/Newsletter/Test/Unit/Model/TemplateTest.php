<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Unit\Model;

use Magento\Framework\App\TemplateTypesInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $design;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var \Magento\Store\Model\App\Emulation|\PHPUnit\Framework\MockObject\MockObject
     */
    private $appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    private $store;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Email\Model\Template\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $emailConfig;

    /**
     * @var \Magento\Email\Model\TemplateFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $templateFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterManager;

    /**
     * @var \Magento\Framework\Url|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlModel;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var \Magento\Newsletter\Model\Template\FilterFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterFactory;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->design = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appEmulation = $this->getMockBuilder(\Magento\Store\Model\App\Emulation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getFrontendName', 'getId', 'getFormattedAddress'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->store->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('frontendName');

        $this->store->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('storeId');

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->assetRepo = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailConfig = $this->getMockBuilder(\Magento\Email\Model\Template\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateFactory = $this->getMockBuilder(\Magento\Email\Model\TemplateFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterManager = $this->getMockBuilder(\Magento\Framework\Filter\FilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlModel = $this->getMockBuilder(\Magento\Framework\Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterFactory = $this->getMockBuilder(\Magento\Newsletter\Model\Template\FilterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Return the model under test with additional methods mocked.
     *
     * @param $mockedMethods array
     * @return \Magento\Newsletter\Model\Template|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getModelMock(array $mockedMethods = [])
    {
        return $this->getMockBuilder(\Magento\Newsletter\Model\Template::class)
            ->setMethods(array_merge($mockedMethods, ['__wakeup', '__sleep', '_init']))
            ->setConstructorArgs(
                [
                    $this->context,
                    $this->design,
                    $this->registry,
                    $this->appEmulation,
                    $this->storeManager,
                    $this->assetRepo,
                    $this->filesystem,
                    $this->scopeConfig,
                    $this->emailConfig,
                    $this->templateFactory,
                    $this->filterManager,
                    $this->urlModel,
                    $this->request,
                    $this->filterFactory,
                ]
            )
            ->getMock();
    }

    public function testGetProcessedTemplateSubject()
    {
        $model = $this->getModelMock(
            [
                'getTemplateFilter',
                'getDesignConfig',
                'applyDesignConfig',
                'setVariables',
            ]
        );

        $templateSubject = 'templateSubject';
        $model->setTemplateSubject($templateSubject);
        $model->setTemplateId('foobar');

        $filterTemplate = $this->createMock(\Magento\Framework\Filter\Template::class);
        $model->expects($this->once())
            ->method('getTemplateFilter')
            ->willReturn($filterTemplate);

        $expectedResult = 'expected';
        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($templateSubject)
            ->willReturn($expectedResult);

        $variables = ['key' => 'value'];
        $filterTemplate->expects($this->once())
            ->method('setVariables')
            ->with(array_merge($variables, ['this' => $model]))
            ->willReturn($filterTemplate);

        $this->assertEquals($expectedResult, $model->getProcessedTemplateSubject($variables));
    }

    /**
     * This test is nearly identical to the
     * \Magento\Email\Test\Unit\Model\AbstractTemplateTest::testGetProcessedTemplate test, except this test also tests
     * to ensure that if a "subscriber" variable is passed to method, the store ID from that object will be used for
     * filtering.
     *
     * @param $variables array
     * @param $templateType string
     * @param $storeId int
     * @param $expectedVariables array
     * @param $expectedResult string
     * @dataProvider getProcessedTemplateDataProvider
     */
    public function testGetProcessedTemplate($variables, $templateType, $storeId, $expectedVariables, $expectedResult)
    {
        class_exists(\Magento\Newsletter\Model\Template\Filter::class, true);
        $filterTemplate = $this->getMockBuilder(\Magento\Newsletter\Model\Template\Filter::class)
            ->setMethods(
                [
                    'setUseSessionInUrl',
                    'setPlainTemplateMode',
                    'setIsChildTemplate',
                    'setDesignParams',
                    'setVariables',
                    'setStoreId',
                    'filter',
                    'getStoreId',
                    'getInlineCssFiles'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $filterTemplate->expects($this->never())
            ->method('setUseSessionInUrl')
            ->with(false)
            ->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setPlainTemplateMode')
            ->with($templateType === TemplateTypesInterface::TYPE_TEXT)
            ->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setIsChildTemplate')
            ->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setDesignParams')
            ->willReturnSelf();
        $filterTemplate->expects($this->any())
            ->method('setStoreId')
            ->willReturnSelf();
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);

        // The following block of code tests to ensure that the store id of the subscriber will be used, if the
        // 'subscriber' variable is set.
        $subscriber = $this->getMockBuilder(\Magento\Newsletter\Model\Subscriber::class)
            ->setMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $subscriber->expects($this->once())
            ->method('getStoreId')
            ->willReturn('3');
        $expectedVariables['subscriber'] = $subscriber;
        $variables['subscriber'] = $subscriber;

        $expectedVariables['store'] = $this->store;
        $model = $this->getModelMock(
            [
                'getDesignParams',
                'applyDesignConfig',
                'getTemplateText',
                'isPlain',
            ]
        );
        $filterTemplate->expects($this->any())
            ->method('setVariables')
            ->with(array_merge(['this' => $model], $expectedVariables));
        $model->setTemplateFilter($filterTemplate);
        $model->setTemplateType($templateType);
        $model->setTemplateId('123');

        $designParams = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $model->expects($this->any())
            ->method('getDesignParams')
            ->willReturn($designParams);

        $model->expects($this->atLeastOnce())
            ->method('isPlain')
            ->willReturn($templateType === TemplateTypesInterface::TYPE_TEXT);

        $preparedTemplateText = $expectedResult; //'prepared text';
        $model->expects($this->once())
            ->method('getTemplateText')
            ->willReturn($preparedTemplateText);

        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($preparedTemplateText)
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $model->getProcessedTemplate($variables));
    }

    /**
     * @return array
     */
    public function getProcessedTemplateDataProvider()
    {
        return [
            'default' => [
                'variables' => [],
                'templateType' => TemplateTypesInterface::TYPE_TEXT,
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
                'variables' => [
                    'logo_url' => 'http://example.com/logo',
                    'logo_alt' => 'Logo Alt',
                ],
                'templateType' => TemplateTypesInterface::TYPE_HTML,
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

    /**
     * @param $senderName string
     * @param $senderEmail string
     * @param $templateSubject string
     * @dataProvider isValidForSendDataProvider
     */
    public function testIsValidForSend($senderName, $senderEmail, $templateSubject, $expectedValue)
    {
        $model = $this->getModelMock(['getTemplateSenderName', 'getTemplateSenderEmail', 'getTemplateSubject']);
        $model->expects($this->any())
            ->method('getTemplateSenderName')
            ->willReturn($senderName);
        $model->expects($this->any())
            ->method('getTemplateSenderEmail')
            ->willReturn($senderEmail);
        $model->expects($this->any())
            ->method('getTemplateSubject')
            ->willReturn($templateSubject);
        $this->assertEquals($expectedValue, $model->isValidForSend());
    }

    /**
     * @return array
     */
    public function isValidForSendDataProvider()
    {
        return [
            'should be valid' => [
                'senderName' => 'sender name',
                'senderEmail' => 'email@example.com',
                'templateSubject' => 'template subject',
                'expectedValue' => true
            ],
            'no sender name so not valid' => [
                'senderName' => '',
                'senderEmail' => 'email@example.com',
                'templateSubject' => 'template subject',
                'expectedValue' => false
            ],
            'no sender email so not valid' => [
                'senderName' => 'sender name',
                'senderEmail' => '',
                'templateSubject' => 'template subject',
                'expectedValue' => false
            ],
            'no subject so not valid' => [
                'senderName' => 'sender name',
                'senderEmail' => 'email@example.com',
                'templateSubject' => '',
                'expectedValue' => false
            ],
        ];
    }
}
