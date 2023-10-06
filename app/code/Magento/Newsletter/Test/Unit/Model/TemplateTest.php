<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model;

use Magento\Email\Model\Template\Config;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\Template;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\DesignInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\Template as NewsletterTemplateModel;
use Magento\Newsletter\Model\Template\Filter;
use Magento\Newsletter\Model\Template\FilterFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Newsletter\Model\Template
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var DesignInterface|MockObject
     */
    private $designMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var Emulation|MockObject
     */
    private $appEmulationMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var Repository|MockObject
     */
    private $assetRepoMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Config|MockObject
     */
    private $emailConfigMock;

    /**
     * @var TemplateFactory|MockObject
     */
    private $templateFactoryMock;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManagerMock;

    /**
     * @var Url|MockObject
     */
    private $urlModelMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var FilterFactory|MockObject
     */
    private $filterFactoryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->designMock = $this->getMockBuilder(DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appEmulationMock = $this->getMockBuilder(Emulation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->setMethods(['getFrontendName', 'getId', 'getFormattedAddress'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('frontendName');

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('storeId');

        $this->storeMock->expects($this->any())
            ->method('getFormattedAddress')
            ->willReturn("Test Store\n Street 1");

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->assetRepoMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->emailConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateFactoryMock = $this->getMockBuilder(TemplateFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlModelMock = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filterFactoryMock = $this->getMockBuilder(FilterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Return the model under test with additional methods mocked.
     *
     * @param $mockedMethods array
     * @return NewsletterTemplateModel|MockObject
     */
    protected function getModelMock(array $mockedMethods = [])
    {
        return $this->getMockBuilder(NewsletterTemplateModel::class)
            ->setMethods(array_merge($mockedMethods, ['__wakeup', '__sleep', '_init']))
            ->setConstructorArgs(
                [
                    $this->contextMock,
                    $this->designMock,
                    $this->registryMock,
                    $this->appEmulationMock,
                    $this->storeManagerMock,
                    $this->assetRepoMock,
                    $this->filesystemMock,
                    $this->scopeConfigMock,
                    $this->emailConfigMock,
                    $this->templateFactoryMock,
                    $this->filterManagerMock,
                    $this->urlModelMock,
                    $this->requestMock,
                    $this->filterFactoryMock,
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

        $filterTemplate = $this->createMock(Template::class);
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
                    'getInlineCssFiles'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $filterTemplate->expects($this->never())
            ->method('setUseSessionInUrl')
            ->with(false)->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setPlainTemplateMode')
            ->with($templateType === TemplateTypesInterface::TYPE_TEXT)->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setIsChildTemplate')->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setDesignParams')->willReturnSelf();
        $filterTemplate->expects($this->any())
            ->method('setStoreId')->willReturnSelf();
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);

        // The following block of code tests to ensure that the store id of the subscriber will be used, if the
        // 'subscriber' variable is set.
        $subscriber = $this->getMockBuilder(Subscriber::class)
            ->setMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $subscriber->expects($this->once())
            ->method('getStoreId')
            ->willReturn('3');
        $expectedVariables['subscriber'] = $subscriber;
        $variables['subscriber'] = $subscriber;

        $expectedVariables['store'] = $this->storeMock;
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
