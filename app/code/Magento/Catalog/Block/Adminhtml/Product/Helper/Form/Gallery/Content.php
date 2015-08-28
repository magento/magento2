<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product form gallery content
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Framework\Data\Form\Element\AbstractElement getElement()
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;

use Magento\Backend\Block\Media\Uploader;
use Magento\Framework\View\Element\AbstractBlock;

class Content extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/helper/gallery.phtml';

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_mediaConfig = $mediaConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild('uploader', 'Magento\Backend\Block\Media\Uploader');

        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->addSessionParam()->getUrl('catalog/product_gallery/upload')
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        $this->_eventManager->dispatch('catalog_product_gallery_prepare_layout', ['block' => $this]);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader block
     *
     * @return Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    /**
     * @return string
     */
    public function getImagesJson()
    {
        if (is_array($this->getElement()->getValue())) {
            $value = $this->getElement()->getValue();
            if (is_array($value['images']) && count($value['images']) > 0) {
                foreach ($value['images'] as &$image) {
                    $image['url'] = $this->_mediaConfig->getMediaUrl($image['file']);
                }
                return $this->_jsonEncoder->encode($value['images']);
            }
        }
        return '[]';
    }

    /**
     * @return string
     */
    public function getImagesValuesJson()
    {
        $values = [];
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $values[$attribute->getAttributeCode()] = $this->getElement()->getDataObject()->getData(
                $attribute->getAttributeCode()
            );
        }
        return $this->_jsonEncoder->encode($values);
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $imageTypes[$attribute->getAttributeCode()] = [
                'code' => $attribute->getAttributeCode(),
                'value' => $this->getElement()->getDataObject()->getData($attribute->getAttributeCode()),
                'label' => $attribute->getFrontend()->getLabel(),
                'scope' => __($this->getElement()->getScopeLabel($attribute)),
                'name' => $this->getElement()->getAttributeFieldName($attribute),
            ];
        }
        return $imageTypes;
    }

    /**
     * @return bool
     */
    public function hasUseDefault()
    {
        foreach ($this->getMediaAttributes() as $attribute) {
            if ($this->getElement()->canDisplayUseDefault($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        return $this->getElement()->getDataObject()->getMediaAttributes();
    }

    /**
     * @return string
     */
    public function getImageTypesJson()
    {
        return $this->_jsonEncoder->encode($this->getImageTypes());
    }
}
