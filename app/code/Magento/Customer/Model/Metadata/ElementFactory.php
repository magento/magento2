<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Form Element Factory
 */
namespace Magento\Customer\Model\Metadata;

/**
 * Class \Magento\Customer\Model\Metadata\ElementFactory
 *
 * @since 2.0.0
 */
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
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.0.0
     */
    protected $_string;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\StringUtils $string
    ) {
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
     * @since 2.0.0
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
            'value' => $value === null ? false : $value,
            'isAjax' => $isAjax,
            'attribute' => $attribute,
        ];
        /** TODO fix when Validation is implemented MAGETWO-17341 */
        if ($dataModelClass == \Magento\Customer\Model\Attribute\Data\Postcode::class) {
            $dataModelClass = \Magento\Customer\Model\Metadata\Form\Postcode::class;
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
