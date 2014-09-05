<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Product\Attribute;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata;
use Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel;
use Magento\Catalog\Service\V1\Product\MetadataServiceInterface;

/**
 * Class WriteService
 * @package Magento\Catalog\Service\V1\Product\Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\Resource\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $helper;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $inputtypeValidatorFactory;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Resource\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Magento\Catalog\Helper\Product $helper
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $inputtypeValidatorFactory
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Resource\Eav\AttributeFactory $attributeFactory,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Catalog\Helper\Product $helper,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $inputtypeValidatorFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->attributeFactory = $attributeFactory;
        $this->filter = $filter;
        $this->helper = $helper;
        $this->inputtypeValidatorFactory = $inputtypeValidatorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(\Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata $attributeMetadata)
    {
        /**
         * @var $model \Magento\Catalog\Model\Resource\Eav\Attribute
         */
        $model = $this->attributeFactory->create();
        $data = $attributeMetadata->__toArray();
        // unset attribute id because we create new attribute (does not rewrite existing one)
        unset($data[AttributeMetadata::ATTRIBUTE_ID]);

        // define frontend label
        if (!$attributeMetadata->getFrontendLabel()) {
            throw InputException::requiredField(AttributeMetadata::FRONTEND_LABEL);
        }
        $data[AttributeMetadata::FRONTEND_LABEL] = [];
        foreach ($attributeMetadata->getFrontendLabel() as $label) {
            $data[AttributeMetadata::FRONTEND_LABEL][$label->getStoreId()] = $label->getLabel();
        }
        if (!isset($data[AttributeMetadata::FRONTEND_LABEL][0]) || !$data[AttributeMetadata::FRONTEND_LABEL][0]) {
            throw InputException::invalidFieldValue(AttributeMetadata::FRONTEND_LABEL, null);
        }

        $data[AttributeMetadata::ATTRIBUTE_CODE] =
            $attributeMetadata->getAttributeCode() ?: $this->generateCode($data[AttributeMetadata::FRONTEND_LABEL][0]);
        $this->validateCode($data[AttributeMetadata::ATTRIBUTE_CODE]);
        $this->validateFrontendInput($attributeMetadata->getFrontendInput());

        $data[AttributeMetadata::BACKEND_TYPE] = $model->getBackendTypeByInput($attributeMetadata->getFrontendInput());
        $data[AttributeMetadata::SOURCE_MODEL] =
            $this->helper->getAttributeSourceModelByInputType($attributeMetadata->getFrontendInput());
        $data[AttributeMetadata::BACKEND_MODEL] =
            $this->helper->getAttributeBackendModelByInputType($attributeMetadata->getFrontendInput());

        $model->addData($data);

        $model->setEntityTypeId($this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId())
            ->setIsUserDefined(1);

        return $model->save()->getAttributeCode();
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, AttributeMetadata $attribute)
    {
        /** @var \Magento\Catalog\Model\Resource\Eav\Attribute $attributeModel */
        $model = $this->attributeFactory->create();
        $model->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $id);
        if (!$model->getId()) {
            throw NoSuchEntityException::singleField(AttributeMetadata::ATTRIBUTE_CODE, $id);
        }

        $data = $attribute->__toArray();

        // this fields should not be changed
        $data[AttributeMetadata::ATTRIBUTE_ID]   = $model->getAttributeId();
        $data[AttributeMetadata::USER_DEFINED]   = $model->getIsUserDefined();
        $data[AttributeMetadata::FRONTEND_INPUT] = $model->getFrontendInput();

        if (isset($data[AttributeMetadata::FRONTEND_LABEL]) && is_array($data[AttributeMetadata::FRONTEND_LABEL])) {
            $frontendLabel[0] = $model->getFrontendLabel();
            foreach ($data[AttributeMetadata::FRONTEND_LABEL] as $item) {
                if (isset($item[FrontendLabel::STORE_ID], $item[FrontendLabel::LABEL])) {
                    $frontendLabel[$item[FrontendLabel::STORE_ID]] = $item[FrontendLabel::LABEL];
                }
            }
            $data[AttributeMetadata::FRONTEND_LABEL] = $frontendLabel;
        }

        if (!$model->getIsUserDefined()) {
            // Unset attribute field for system attributes
            unset($data[AttributeMetadata::APPLY_TO]);
        }

        try {
            $model->addData($data);
            $model->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not update product attribute' . $e->getMessage());
        }

        return $model->getAttributeCode();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($attributeId)
    {
        $model = $this->eavConfig->getAttribute(MetadataServiceInterface::ENTITY_TYPE, $attributeId);
        if (!$model || !$model->getId()) {
            //product attribute does not exist
            throw NoSuchEntityException::singleField(AttributeMetadata::ATTRIBUTE_ID, $attributeId);
        }
        $model->delete();
        return true;
    }

    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     */
    protected function generateCode($label)
    {
        $code = substr(preg_replace('/[^a-z_0-9]/', '_', $this->filter->translitUrl($label)), 0, 30);
        $validatorAttrCode = new \Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/'));
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(md5(time()), 0, 8));
        }
        return $code;
    }

    /**
     * Validate attribute code
     *
     * @param string $code
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function validateCode($code)
    {
        $validatorAttrCode = new \Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{0,30}$/'));
        if (!$validatorAttrCode->isValid($code)) {
            throw InputException::invalidFieldValue('attribute_code', $code);
        }
    }

    /**
     * Validate Frontend Input Type
     *
     * @param  string $frontendInput
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function validateFrontendInput($frontendInput)
    {
        $validator = $this->inputtypeValidatorFactory->create();
        if (!$validator->isValid($frontendInput)) {
            throw InputException::invalidFieldValue('frontend_input', $frontendInput);
        }
    }
}
