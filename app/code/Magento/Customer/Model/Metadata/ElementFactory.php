<?php
/**
 * Customer Form Element Factory
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model\Metadata;

class ElementFactory
{
    const OUTPUT_FORMAT_JSON    = 'json';
    const OUTPUT_FORMAT_TEXT    = 'text';
    const OUTPUT_FORMAT_HTML    = 'html';
    const OUTPUT_FORMAT_PDF     = 'pdf';
    const OUTPUT_FORMAT_ONELINE = 'oneline';
    const OUTPUT_FORMAT_ARRAY   = 'array'; // available only for multiply attributes

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Stdlib\String
     */
    protected $_string;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Stdlib\String $string
     */
    public function __construct(\Magento\ObjectManager $objectManager, \Magento\Stdlib\String $string)
    {
        $this->_objectManager = $objectManager;
        $this->_string = $string;
    }

    /**
     * Create Form Element
     *
     * @param \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata $attribute
     * @param $value
     * @param $entityTypeCode
     * @param bool $isAjax
     * @return \Magento\Customer\Model\Metadata\Form\AbstractData
     */
    public function create(
        \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata $attribute,
        $value,
        $entityTypeCode,
        $isAjax = false
    ) {
        $dataModelClass = $attribute->getDataModel();
        $params = [
            'entityTypeCode' => $entityTypeCode,
            'value' => is_null($value) ? false : $value,
            'isAjax' => $isAjax,
            'attribute' => $attribute
        ];
        /** TODO fix when Validation is implemented MAGETWO-17341 */
        if ($dataModelClass == 'Magento\Customer\Model\Attribute\Data\Postcode') {
            $dataModelClass = 'Magento\Customer\Model\Metadata\Form\Text';
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
