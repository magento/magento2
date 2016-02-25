<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog category image attribute backend model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Category\Attribute\Backend;

class Image extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Image uploader helper
     *
     * @var \Magento\Catalog\Helper\ImageUploader
     */
    protected $imageUploaderHelper;

    /**
     * Image constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Helper\ImageUploader $imageUploaderHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Helper\ImageUploader $imageUploaderHelper
    ) {
        $this->imageUploaderHelper = $imageUploaderHelper;
        $this->logger = $logger;
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName() . '_additional_data');

        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
            return $this;
        }

        if (isset($value[0]['name']) && isset($value[0]['tmp_name'])) {
            try {
                $result = $this->imageUploaderHelper->moveFileFromTmp($value[0]['name']);

                $object->setData($this->getAttribute()->getName(), $result);
                $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $this->logger->critical($e);
                }
            }
        }
        return $this;
    }
}
