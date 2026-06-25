<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea adminhtml
     */
    public function testGetAfterElementHtml()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Layout::class,
            ['area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE]
        );
        $authorization = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\AuthorizationInterface::class,
            ['aclPolicy' =>  new \Magento\Framework\Authorization\Policy\DefaultPolicy()]
        );

        $block = $objectManager->create(
            \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category::class,
            ['layout' => $layout, 'authorization' => $authorization]
        );

        /** @var $formFactory \Magento\Framework\Data\FormFactory */
        $formFactory = $objectManager->get(\Magento\Framework\Data\FormFactory::class);
        $form = $formFactory->create();
        $block->setForm($form);

        $this->assertMatchesRegularExpression('/<button[^>]*New\sCategory[^>]*>/', $block->getAfterElementHtml());
    }
}
