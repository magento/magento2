<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Contact\Block;

use Magento\Contact\ViewModel\UserDataProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\ButtonLockManager;
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        Bootstrap::getInstance()->loadArea('frontend');
        $this->block = Bootstrap::getObjectManager()->create(ContactForm::class)
            ->setButtonLockManager(Bootstrap::getObjectManager()->create(ButtonLockManager::class));
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

    public static function dataProvider(): array
    {
        return [
            'view model was not preset before' => [
                'setViewModel' => false,
                'expectedViewModelType' => UserDataProvider::class
            ],
            'view model was pre-installed before' => [
                'setViewModel' => true,
                'expectedViewModelType' => self::SOME_VIEW_MODEL
            ]
        ];
    }
}
