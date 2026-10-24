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
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @param bool $setViewModel
     * @param string $expectedViewModelType
     */
    #[DataProvider('dataProvider')]
    public function testViewModel($setViewModel, $expectedViewModelType)
    {
        if ($setViewModel) {
            $someViewModel = $this->createMock(ArgumentInterface::class);
            $this->block->setData('view_model', $someViewModel);
        }

        $this->block->toHtml();

        $viewModel = $this->block->getData('view_model');
        if ($setViewModel) {
            // When a view model was pre-set, verify it wasn't replaced
            $this->assertInstanceOf(ArgumentInterface::class, $viewModel);
            $this->assertNotInstanceOf(UserDataProvider::class, $viewModel);
        } else {
            // When no view model was set, verify the default UserDataProvider was added
            $this->assertInstanceOf($expectedViewModelType, $viewModel);
        }
    }

    public static function dataProvider(): array
    {
        return [
            'view model was not preset before' => [
                false,  // $setViewModel
                UserDataProvider::class  // $expectedViewModelType
            ],
            'view model was pre-installed before' => [
                true,  // $setViewModel
                ArgumentInterface::class  // $expectedViewModelType
            ]
        ];
    }
}
