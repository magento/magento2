<?php
/**
 * Customer Form Element Factory
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Model\Metadata;

class ElementFactory
{
    const OUTPUT_FORMAT_JSON = 'json';

    const OUTPUT_FORMAT_TEXT = 'text';

    const OUTPUT_FORMAT_HTML = 'html';

    const OUTPUT_FORMAT_PDF = 'pdf';

    const OUTPUT_FORMAT_ONELINE = 'oneline';

    const OUTPUT_FORMAT_ARRAY = 'array';

    // available only for multiply attributes

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\Stdlib\StringUtils $string)
    {
        $this->_objectManager = $objectManager;
        $this->_string = $string;
    }

    /**
     * Create Form Element
     *
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @param string|int|bool $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @return \Magento\Customer\Model\Metadata\Form\AbstractData
     */
    public function create(
        \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute,
        $value,
        $entityTypeCode,
        $isAjax = false
    ) {
        $dataModelClass = $attribute->getDataModel();
        $params = [
            'entityTypeCode' => $entityTypeCode,
            'value' => is_null($value) ? false : $value,
            'isAjax' => $isAjax,
            'attribute' => $attribute,
        ];
        /** TODO fix when Validation is implemented MAGETWO-17341 */
        if ($dataModelClass == 'Magento\Customer\Model\Attribute\Data\Postcode') {
            $dataModelClass = 'Magento\Customer\Model\Metadata\Form\Postcode';
        }
        if (!empty($dataModelClass)) {
            $dataModel = $this->_objectManager->create($dataModelClass, $params);
        } else {
            $dataModelClass = sprintf(
                'Magento\Customer\Model\Metadata\Form\%s',
                $this->_string->upperCaseWords($attribute->getFrontendInput())
            );
            $dataModel = $this->_objectManager->create($dataModelClass, $params);
        }

        return $dataModel;
    }
}
