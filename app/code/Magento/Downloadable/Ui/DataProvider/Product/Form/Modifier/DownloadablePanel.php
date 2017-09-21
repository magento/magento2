<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Downloadable\Api\Data\ProductAttributeInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;

/**
 * Class adds Downloadable collapsible panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadablePanel extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(LocatorInterface $locator, ArrayManager $arrayManager)
    {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();

        $data[$model->getId()][ProductAttributeInterface::CODE_IS_DOWNLOADABLE] =
            ($model->getTypeId() === Type::TYPE_DOWNLOADABLE) ? '1' : '0';

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $panelConfig['arguments']['data']['config'] = [
            'componentType' => Form\Fieldset::NAME,
            'label' => __('Downloadable Information'),
            'collapsible' => true,
            'opened' => $this->locator->getProduct()->getTypeId() === Type::TYPE_DOWNLOADABLE,
            'sortOrder' => '800',
            'dataScope' => 'data'
        ];
        $this->meta = $this->arrayManager->set('downloadable', $this->meta, $panelConfig);

        $this->addCheckboxIsDownloadable();
        $this->addMessageBox();

        return $this->meta;
    }

    /**
     * Add message
     *
     * @return void
     */
    protected function addMessageBox()
    {
        $messagePath = Composite::CHILDREN_PATH . '/downloadable_message';
        $messageConfig['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/html',
            'additionalClasses' => 'admin__fieldset-note',
            'content' => __('To enable the option set the weight to no'),
            'sortOrder' => 20,
            'visible' => false,
            'imports' => [
                'visible' => '${$.provider}:' . self::DATA_SCOPE_PRODUCT . '.'
                    . ProductAttributeInterface::CODE_HAS_WEIGHT
            ],
        ];

        $this->meta = $this->arrayManager->set($messagePath, $this->meta, $messageConfig);
    }

    /**
     * Add Checkbox
     *
     * @return void
     */
    protected function addCheckboxIsDownloadable()
    {
        $checkboxPath = Composite::CHILDREN_PATH . '/' . ProductAttributeInterface::CODE_IS_DOWNLOADABLE;
        $checkboxConfig['arguments']['data']['config'] = [
            'dataType' => Form\Element\DataType\Number::NAME,
            'formElement' => Form\Element\Checkbox::NAME,
            'componentType' => Form\Field::NAME,
            'component' => 'Magento_Downloadable/js/components/is-downloadable-handler',
            'description' => __('Is this downloadable Product?'),
            'dataScope' => ProductAttributeInterface::CODE_IS_DOWNLOADABLE,
            'sortOrder' => 10,
            'imports' => [
                'disabled' => '${$.provider}:' . self::DATA_SCOPE_PRODUCT . '.'
                    . ProductAttributeInterface::CODE_HAS_WEIGHT
            ],
            'valueMap' => [
                'false' => '0',
                'true' => '1',
            ],
            'samplesFieldset' => 'ns = ${ $.ns }, index=' . Composite::CONTAINER_SAMPLES,
            'linksFieldset' => 'ns = ${ $.ns }, index=' . Composite::CONTAINER_LINKS,
        ];

        $this->meta = $this->arrayManager->set($checkboxPath, $this->meta, $checkboxConfig);
    }
}
