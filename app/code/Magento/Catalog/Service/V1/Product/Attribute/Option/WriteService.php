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
namespace Magento\Catalog\Service\V1\Product\Attribute\Option;

use Magento\Catalog\Service\V1\Product\MetadataServiceInterface as ProductMetadataServiceInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Service\V1\Data\Eav\Option as EavOption;
use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata;

class WriteService implements WriteServiceInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption($id, EavOption $option)
    {
        $model = $this->eavConfig->getAttribute(ProductMetadataServiceInterface::ENTITY_TYPE, $id);
        if (!$model || !$model->getId()) {
            throw NoSuchEntityException::singleField(AttributeMetadata::ATTRIBUTE_ID, $id);
        }

        if (!$model->usesSource()) {
            throw new StateException('Attribute doesn\'t have any option');
        }

        $key = 'new_option';

        $options = [];
        $options['value'][$key][0] = $option->getLabel();
        $options['order'][$key] = $option->getOrder();

        if (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                $options['value'][$key][$label->getStoreId()] = $label->getLabel();
            }
        }

        if ($option->isDefault()) {
            $model->setDefault([$key]);
        }

        $model->setOption($options);
        $model->save();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOption($id, $optionId)
    {
        $model = $this->eavConfig->getAttribute(ProductMetadataServiceInterface::ENTITY_TYPE, $id);
        if (!$model || !$model->getId()) {
            throw NoSuchEntityException::singleField(AttributeMetadata::ATTRIBUTE_ID, $id);
        }
        if (!$model->usesSource()) {
            throw new StateException('Attribute doesn\'t have any option');
        }
        if (!$model->getSource()->getOptionText($optionId)) {
            throw new NoSuchEntityException(sprintf('Attribute %s does not contain option with Id %s', $id, $optionId));
        }

        $modelData = array('option' => array('value' => array($optionId => []), 'delete' => array($optionId => '1')));
        $model->addData($modelData);
        try {
            $model->save();
        } catch (\Exception $e) {
            throw new StateException('Unable to remove option');
        }

        return true;
    }
}
