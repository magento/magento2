<?php
/**
 *
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Controller\Adminhtml\Product\NewAction;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTestCase;
use Magento\Framework\RegexValidator;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\RegexFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;

class NewActionTest extends ProductTestCase
{
    /** @var NewAction */
    protected $action;

    /** @var Page|MockObject */
    protected $resultPage;

    /** @var Forward|MockObject */
    protected $resultForward;

    /** @var Builder|MockObject */
    protected $productBuilder;

    /** @var Product|MockObject */
    protected $product;

    /**
     * @var Helper|MockObject
     */
    protected $initializationHelper;

    /**
     * @var RegexValidator|MockObject
     */
    private $regexValidator;

    /**
     * @var RegexFactory
     */
    private $regexValidatorFactoryMock;

    /**
     * @var Regex|MockObject
     */
    private $regexValidatorMock;

    /**
     * @var ForwardFactory&MockObject|MockObject
     */
    private $resultForwardFactory;

    protected function setUp(): void
    {
        $this->productBuilder = $this->createPartialMock(
            Builder::class,
            ['build']
        );
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addData', 'getTypeId', 'getStoreId', '__sleep'])->getMock();
        $this->product->expects($this->any())->method('getTypeId')->willReturn('simple');
        $this->product->expects($this->any())->method('getStoreId')->willReturn('1');
        $this->productBuilder->expects($this->any())->method('build')->willReturn($this->product);

        $this->resultPage = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactory = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->regexValidatorFactoryMock = $this->getMockBuilder(RegexFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->regexValidatorMock = $this->createMock(Regex::class);
        $this->regexValidatorFactoryMock->method('create')
            ->willReturn($this->regexValidatorMock);

        $this->regexValidator = new regexValidator($this->regexValidatorFactoryMock);
        $this->action = (new ObjectManager($this))->getObject(
            NewAction::class,
            [
                'context' => $this->initContext(),
                'productBuilder' => $this->productBuilder,
                'resultPageFactory' => $resultPageFactory,
                'resultForwardFactory' => $this->resultForwardFactory,
                'regexValidator' => $this->regexValidator,
            ]
        );
    }

    /**
     * Test execute method input validation.
     *
     * @param string $value
     * @param bool $exceptionThrown
     * @dataProvider validationCases
     */
    public function testExecute(string $value, bool $exceptionThrown): void
    {
        if ($exceptionThrown) {
            $this->action->getRequest()->expects($this->any())
                ->method('getParam')
                ->willReturn($value);
            $this->resultForwardFactory->expects($this->any())
                ->method('create')
                ->willReturn($this->resultForward);
            $this->resultForward->expects($this->once())
                ->method('forward')
                ->with('noroute')
                ->willReturn(true);
            $this->assertTrue($this->action->execute());
        } else {
            $this->action->getRequest()->expects($this->any())->method('getParam')->willReturn($value);
            $this->regexValidatorMock->expects($this->any())
                ->method('isValid')
                ->with($value)
                ->willReturn(true);

            $this->assertEquals(true, $this->regexValidator->validateParamRegex($value));
        }
    }

    /**
     * Validation cases.
     *
     * @return array
     */
    public static function validationCases(): array
    {
        return [
            'execute-with-exception' => ['simple\' and true()]|*[self%3a%3ahandle%20or%20self%3a%3alayout',true],
            'execute-without-exception' => ['catalog_product_new',false]
        ];
    }
}
