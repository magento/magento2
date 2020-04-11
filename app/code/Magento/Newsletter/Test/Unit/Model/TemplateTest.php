<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Unit\Model;

use Magento\Email\Model\Template\Config;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\DesignInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\Template;
use Magento\Newsletter\Model\Template\Filter;
use Magento\Newsletter\Model\Template\FilterFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var DesignInterface|MockObject
     */
    private $design;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var Emulation|MockObject
     */
    private $appEmulation;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Config|MockObject
     */
    private $emailConfig;

    /**
     * @var TemplateFactory|MockObject
     */
    private $templateFactory;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManager;

    /**
     * @var Url|MockObject
     */
    private $urlModel;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var FilterFactory|MockObject
     */
    private $filterFactory;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->design = $this->getMockBuilder(DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appEmulation = $this->getMockBuilder(Emulation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(Store::class)
            ->setMethods(['getFrontendName', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->store->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontendName'));

        $this->store->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('storeId'));

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->store));

        $this->assetRepo = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateFactory = $this->getMockBuilder(TemplateFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterManager = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlModel = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterFactory = $this->getMockBuilder(FilterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Return the model under test with additional methods mocked.
     *
     * @param $mockedMethods array
     * @return Template|MockObject
     */
    protected function getModelMock(array $mockedMethods = [])
    {
        return $this->getMockBuilder(Template::class)
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
            ->will($this->returnValue($filterTemplate));

        $expectedResult = 'expected';
        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($templateSubject)
            ->will($this->returnValue($expectedResult));

        $filterTemplate->expects($this->exactly(2))
            ->method('setStrictMode')
            ->withConsecutive([$this->equalTo(false)], [$this->equalTo(true)])
            ->willReturnOnConsecutiveCalls(true, false);

        $variables = ['key' => 'value'];
        $filterTemplate->expects($this->once())
            ->method('setVariables')
            ->with(array_merge($variables, ['this' => $model]))
            ->will($this->returnValue($filterTemplate));

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
        class_exists(Filter::class, true);
        $filterTemplate = $this->getMockBuilder(Filter::class)
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
                    'getInlineCssFiles',
                    'setStrictMode',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $filterTemplate->expects($this->never())
            ->method('setUseSessionInUrl')
            ->with(false)
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setPlainTemplateMode')
            ->with($templateType === TemplateTypesInterface::TYPE_TEXT)
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setIsChildTemplate')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setDesignParams')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));

        $filterTemplate->expects($this->exactly(2))
            ->method('setStrictMode')
            ->withConsecutive([$this->equalTo(true)], [$this->equalTo(false)])
            ->willReturnOnConsecutiveCalls(false, true);

        // The following block of code tests to ensure that the store id of the subscriber will be used, if the
        // 'subscriber' variable is set.
        $subscriber = $this->getMockBuilder(Subscriber::class)
            ->setMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $subscriber->expects($this->once())
            ->method('getStoreId')
            ->will($this->returnValue('3'));
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
            'area' => Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $model->expects($this->any())
            ->method('getDesignParams')
            ->will($this->returnValue($designParams));

        $model->expects($this->atLeastOnce())
            ->method('isPlain')
            ->will($this->returnValue($templateType === TemplateTypesInterface::TYPE_TEXT));

        $preparedTemplateText = $expectedResult; //'prepared text';
        $model->expects($this->once())
            ->method('getTemplateText')
            ->will($this->returnValue($preparedTemplateText));

        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($preparedTemplateText)
            ->will($this->returnValue($expectedResult));

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
            ->will($this->returnValue($senderName));
        $model->expects($this->any())
            ->method('getTemplateSenderEmail')
            ->will($this->returnValue($senderEmail));
        $model->expects($this->any())
            ->method('getTemplateSubject')
            ->will($this->returnValue($templateSubject));
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
