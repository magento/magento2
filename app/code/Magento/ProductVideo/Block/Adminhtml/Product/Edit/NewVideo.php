<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Block\Adminhtml\Product\Edit;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class NewVideo extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->setUseContainer(true);
    }

    /**
     * Form preparation
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'new_video_form',
                    'class' => 'admin__scope-old',
                    'enctype' => 'multipart/form-data',
                ]
            ]
        );
        $form->setUseContainer($this->getUseContainer());

        $form->addField('new_video_messages', 'note', []);

        $fieldset = $form->addFieldset('new_video_form_fieldset', []);

        $fieldset->addField(
            '',
            'hidden',
            [
                'name' => 'form_key',
                'value' => $this->getFormKey(),
            ]
        );

        $fieldset->addField(
            'item_id',
            'hidden',
            []
        );

        $fieldset->addField(
            'new_video_url',
            'text',
            [
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => true,
                'name' => 'new_video_url',
                'note' => 'Youtube or Vimeo supported',
            ]
        );


        $fieldset->addField(
            'new_video_name',
            'text',
            [
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'name' => 'new_video_name',
            ]
        );

        $fieldset->addField(
            'new_video_description',
            'textarea',
            [
                'label' => __('Description'),
                'title' => __('Description'),
                'name' => 'new_video_description',
            ]
        );

        $fieldset->addField(
            'new_video_screenshot',
            'file',
            [
                'label' => __('Preview Image'),
                'title' => __('Preview Image'),
                'name' => 'image',
            ]
        );

        $fieldset->addField(
            'new_video_get',
            'button',
            [
                'label' => '',
                'title' => __('Get Video Information'),
                'name' => 'new_video_get',
                'value' => 'Get Video Information',
            ]
        );

        $fieldset->addField(
            'new_video_disabled',
            'checkbox',
            [
                'label' => 'Hide from Product Page',
                'title' => __('Hide from Product Page'),
                'name' => 'new_video_disabled',
            ]
        );

        $fieldset->addField(
            'new_video_role',
            'checkbox',
            [
                'label' => 'Base Image',
                'title' => __('Base Image'),
                'data-role' => 'type-selector',
            ]
        );

        $this->setForm($form);
    }

    public function getHtmlId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->mathRandom->getUniqueHash('id_'));
        }
        return $this->getData('id');
    }

    /**
     * Attach new video dialog widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $widgetOptions = $this->_jsonEncoder->encode(
            [
                'saveVideoUrl' => $this->getUrl('catalog/product_gallery/upload'),
                'htmlId' => $this->getHtmlId(),
            ]
        );
        //TODO: JavaScript logic should be moved to separate file or reviewed
        return <<<HTML
<script>
require(["jquery","mage/mage"],function($) {  // waiting for dependencies at first
    $(function(){ // waiting for page to load to have '#video_ids-template' available
        $('#new-video').mage('newVideoDialog', $widgetOptions);
    });
});
</script>
HTML;
    }
}
