<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Block\Adminhtml\Template\Edit;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\TemplateTypesInterface;

/**
 * Test class for \Magento\Email\Block\Adminhtml\Template\Edit\Form
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /** @var string[] */
    protected $expectedFields;

    /** @var \Magento\Email\Model\Template */
    protected $template;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Email\Block\Adminhtml\Template\Edit\Form */
    protected $block;

    /** @var \ReflectionMethod */
    protected $prepareFormMethod;

    public function setUp()
    {
        $this->expectedFields = [
            'base_fieldset',
            'template_code',
            'template_subject',
            'orig_template_variables',
            'variables',
            'template_variables',
            'insert_variable',
            'template_text',
            'template_styles'
        ];

        $this->objectManager = Bootstrap::getObjectManager();
        $this->template = $this->objectManager->get(\Magento\Email\Model\Template::class)
            ->setId(1)
            ->setTemplateType(TemplateTypesInterface::TYPE_HTML);

        $this->block = $this->objectManager->create(
            \Magento\Email\Block\Adminhtml\Template\Edit\Form::class,
            [
                'data' => [
                    'email_template' => $this->template
                ]
            ]
        );
        $this->prepareFormMethod = new \ReflectionMethod(
            \Magento\Email\Block\Adminhtml\Template\Edit\Form::class,
            '_prepareForm'
        );
        $this->prepareFormMethod->setAccessible(true);
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Edit\Form::_prepareForm
     */
    public function testPrepareFormWithTemplateId()
    {
        $this->expectedFields[] = 'currently_used_for';
        $this->runTest();
    }

    protected function runTest()
    {
        $this->prepareFormMethod->invoke($this->block);
        $form = $this->block->getForm();
        foreach ($this->expectedFields as $key) {
            $this->assertNotNull($form->getElement($key));
        }
        $this->assertGreaterThan(0, strpos($form->getElement('insert_variable')->getData('text'), 'Insert Variable'));
    }
}
