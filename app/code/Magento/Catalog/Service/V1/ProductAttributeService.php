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
namespace Magento\Catalog\Service\V1;

use Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory;

/**
 * Class ProductAttributeService
 */
class ProductAttributeService implements ProductAttributeServiceInterface
{
    /**
     * @var ProductMetadataServiceInterface
     */
    private $metadataService;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /** @var  \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory */
    private $optionCollectionFactory;

    /**
     * @param ProductMetadataServiceInterface $metadataService
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $optionCollectionFactory
     */
    public function __construct(
        ProductMetadataServiceInterface $metadataService,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $optionCollectionFactory
    ) {
        $this->metadataService = $metadataService;
        $this->eavConfig = $eavConfig;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function options($id)
    {
        return $this->metadataService->getAttributeMetadata(
            ProductMetadataServiceInterface::ENTITY_TYPE_PRODUCT,
            $id
        )->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function addOption($id, \Magento\Catalog\Service\V1\Data\Eav\Option $option)
    {
        $model = $this->eavConfig->getAttribute(
            \Magento\Catalog\Service\V1\ProductMetadataServiceInterface::ENTITY_TYPE_PRODUCT,
            $id
        );
        if (!$model) {
            throw new \Magento\Framework\Exception\StateException('Attribute does no exist');
        }

        if (!$model->usesSource()) {
            throw new \Magento\Framework\Exception\StateException('Attribute doesn\'t have any option');
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
}
