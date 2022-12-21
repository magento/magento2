<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Block;

use Magento\Contact\ViewModel\UserDataProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Testing behavior when view model was not preset before
 * and view model was pre-installed before
 */
class ContactFormTest extends TestCase
{
    /**
     * Some classname
     */
    private const SOME_VIEW_MODEL = 'Magento_Contact_ViewModel_Some_View_Model';

    /**
     * @var ContactForm
     */
    private $block;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        Bootstrap::getInstance()->loadArea('frontend');
        $this->block = Bootstrap::getObjectManager()->create(ContactForm::class);
    }

    /**
     * @param $setViewModel
     * @param $expectedViewModelType
     *
     * @dataProvider dataProvider
     */
    public function testViewModel($setViewModel, $expectedViewModelType)
    {
        if ($setViewModel) {
            $someViewModel = $this->getMockForAbstractClass(
                ArgumentInterface::class,
                [],
                self::SOME_VIEW_MODEL
            );
            $this->block->setData('view_model', $someViewModel);
        }

        $this->block->toHtml();

        $this->assertInstanceOf($expectedViewModelType, $this->block->getData('view_model'));
    }

    public function dataProvider(): array
    {
        return [
            'view model was not preset before' => [
                'set view model' => false,
                'expected view model type' => UserDataProvider::class
            ],
            'view model was pre-installed before' => [
                'set view model' => true,
                'expected view model type' => self::SOME_VIEW_MODEL
            ]
        ];
    }
}
