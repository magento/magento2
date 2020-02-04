<?php

namespace Alexx\Blog\Block\Adminhtml\Index\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\App\Action\Context as ActionContext;

/**
 * Class Form
 */
class Form extends Generic
{
    use \Alexx\Blog\Controller\Adminhtml\UseFunctions;

    private $_wysiwygConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param ActionContext $actionContext
     * @param array $data
     */

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        ActionContext $actionContext,
        array $data = []
    ) {
        $this->_objectManager = $actionContext->getObjectManager();
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Form fields options
     * */
    protected function _prepareForm()
    {
        /** @var Magento\Framework\Data\Form $form */
        $model = $this->getCurrentRegistry('blognews');

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General')]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'entity_id',
                'hidden',
                ['name' => 'blog_data[entity_id]']
            );
        }
        $fieldset->addField(
            'theme',
            'text',
            [
                'name' => 'blog_data[theme]',
                'label' => __('Title'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'picture',
            'image',
            [
                'name' => 'blog_picture',
                'label' => __('Picture'),
                'required' => false,
            ]
        );

        $wysiwygConfig = $this->_wysiwygConfig->getConfig();
        $fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'blog_data[content]',
                'label' => __('Content'),
                'required' => true,
                'config' => $wysiwygConfig
            ]
        );

        $data = $model->getData();
        $form->setValues($data);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
