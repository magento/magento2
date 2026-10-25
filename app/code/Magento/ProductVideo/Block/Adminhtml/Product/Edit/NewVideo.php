<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\ProductVideo\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class NewVideo extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Anchor is product video
     */
    public const PATH_ANCHOR_PRODUCT_VIDEO = 'catalog_product_video-link';

    /**
     * @var \Magento\ProductVideo\Helper\Media
     */
    protected $mediaHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var string
     */
    protected $videoSelector = '#media_gallery_content';

    /**
     * @var array
     */
    private array $renderers = [];

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\ProductVideo\Helper\Media $mediaHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\ProductVideo\Helper\Media $mediaHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->mediaHelper = $mediaHelper;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->jsonEncoder = $jsonEncoder;
        $this->setUseContainer(true);
    }

    /**
     * Form preparation
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $showUseDefault = ((int) $this->getProduct()->getStoreId()) !== Store::DEFAULT_STORE_ID;
        
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'new_video_form',
                'class' => 'admin__scope-old',
                'enctype' => 'multipart/form-data',
            ]
        ]);
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
            'file_name',
            'hidden',
            []
        );
        $fieldset->addField(
            'video_provider',
            'hidden',
            [
                'name' => 'video_provider',
            ]
        );
        $fieldset->addField(
            'video_url',
            'text',
            [
                'class' => 'edited-data validate-url',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => true,
                'name' => 'video_url',
                'note' => $this->getNoteVideoUrl(),
            ]
        );
        $fieldset->addField(
            'video_title',
            'text',
            [
                'class' => 'edited-data',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'name' => 'video_title',
                'scope_label' => __('[STORE VIEW]'),
                ...($showUseDefault ? ['use_default' => false] : [])
            ]
        )->setRenderer($this->getFieldRenderer('text'));
        $fieldset->addField(
            'video_description',
            'textarea',
            [
                'class' => 'edited-data',
                'label' => __('Description'),
                'title' => __('Description'),
                'name' => 'video_description',
                'scope_label' => __('[STORE VIEW]'),
                ...($showUseDefault ? ['use_default' => false] : [])
            ],
        )->setRenderer($this->getFieldRenderer('textarea'));
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
            'new_video_screenshot_preview',
            'button',
            [
                'class' => 'preview-image-hidden-input',
                'label' => '',
                'name' => '_preview',
            ]
        );
        $fieldset->addField(
            'new_video_get',
            'button',
            [
                'label' => '',
                'title' => 'Get Video Information',
                'name' => 'new_video_get',
                'value' => __('Get Video Information'),
                'class' => 'action-default'
            ]
        );
        $this->addMediaRoleAttributes($fieldset);
        $fieldset->addField(
            'new_video_disabled',
            'checkbox',
            [
                'class' => 'edited-data',
                'label' => __('Hide from Product Page'),
                'title' => __('Hide from Product Page'),
                'name' => 'disabled',
                'scope_label' => __('[STORE VIEW]'),
                ...($showUseDefault ? ['use_default' => false] : [])
            ]
        )->setRenderer($this->getFieldRenderer('checkbox'));
        $this->setForm($form);
    }

    /**
     * Get html id
     *
     * @return mixed
     */
    public function getHtmlId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->mathRandom->getUniqueHash('id_'));
        }
        return $this->getData('id');
    }

    /**
     * Get widget options
     *
     * @return string
     */
    public function getWidgetOptions()
    {
        return $this->jsonEncoder->encode(
            [
                'saveVideoUrl' => $this->getUrl('catalog/product_gallery/upload'),
                'saveRemoteVideoUrl' => $this->getUrl('product_video/product_gallery/retrieveImage'),
                'htmlId' => $this->getHtmlId(),
                'youTubeApiKey' => $this->mediaHelper->getYouTubeApiKey(),
                'videoSelector' => $this->videoSelector
            ]
        );
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Add media role attributes to fieldset
     *
     * @param Fieldset $fieldset
     * @return $this
     */
    protected function addMediaRoleAttributes(Fieldset $fieldset)
    {
        
        $fieldset = $fieldset->addFieldset('media_roles_fieldset', []);
        $fieldset->addField('role-label', 'note', ['text' => __('Role')]);
        $mediaRoles = $this->getProduct()->getMediaAttributes();
        ksort($mediaRoles);
        foreach ($mediaRoles as $mediaRole) {
            $showUseDefault = ((int) $this->getProduct()->getStoreId()) !== Store::DEFAULT_STORE_ID
                && !$mediaRole->isScopeGlobal();
            $fieldset->addField(
                'video_' . $mediaRole->getAttributeCode(),
                'checkbox',
                [
                    'class' => 'video_image_role',
                    'label' => __($mediaRole->getFrontendLabel()),
                    'title' => __($mediaRole->getFrontendLabel()),
                    'data-role' => 'role-type-selector',
                    'value' => $mediaRole->getAttributeCode(),
                    'scope_label' => match ($mediaRole->getIsGlobal()) {
                        ScopedAttributeInterface::SCOPE_GLOBAL => __('[GLOBAL]'),
                        ScopedAttributeInterface::SCOPE_WEBSITE => __('[WEBSITE]'),
                        default => __('[STORE VIEW]')
                    },
                    ...($showUseDefault ? ['use_default' => false] : [])
                ]
            )->setRenderer($this->getFieldRenderer('checkbox'));
        }
        return $this;
    }

    /**
     * Get note for video url
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getNoteVideoUrl()
    {
        $result = __('YouTube and Vimeo supported.');
        if ($this->mediaHelper->getYouTubeApiKey() === null) {
            $result = __(
                'Vimeo supported.<br />'
                . 'To add YouTube video, please <a href="%1">enter YouTube API Key</a> first.',
                $this->getConfigApiKeyUrl()
            );
        }
        return $result;
    }

    /**
     * Get url for config params
     *
     * @return string
     */
    protected function getConfigApiKeyUrl()
    {
        return $this->urlBuilder->getUrl(
            'adminhtml/system_config/edit',
            [
                'section' => 'catalog',
                '_fragment' => self::PATH_ANCHOR_PRODUCT_VIDEO
            ]
        );
    }

    /**
     * Returns field renderer block by type
     *
     * @param string $type
     * @return Element
     */
    private function getFieldRenderer(string $type): Element
    {
        if (!isset($this->renderers[$type])) {
            $this->renderers[$type] = match ($type) {
                'textarea', 'text' => $this->getLayout()->createBlock(
                    Element::class,
                    $this->getNameInLayout() . '_fieldset_element_' . $type,
                    [
                        'data' => [
                            'template' =>
                                'Magento_ProductVideo::product/edit/slideout/form/renderer/fieldset/element.phtml'
                        ]
                    ]
                ),
                'checkbox' => $this->getLayout()->createBlock(
                    Element::class,
                    $this->getNameInLayout() . '_fieldset_element_' . $type,
                    [
                        'data' => [
                            'template' =>
                                'Magento_ProductVideo::product/edit/slideout/form/renderer/fieldset/switcher.phtml'
                        ]
                    ]
                ),
            };
        }
        return $this->renderers[$type];
    }
}
