<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Source\TypeUpload;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;

/**
 * Class adds a grid with samples.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Samples extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var TypeUpload
     */
    protected $typeUpload;

    /**
     * @var Data\Samples
     */
    protected $samplesData;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param TypeUpload $typeUpload
     * @param Data\Samples $samplesData
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        TypeUpload $typeUpload,
        Data\Samples $samplesData
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->typeUpload = $typeUpload;
        $this->samplesData = $samplesData;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();

        $data[$model->getId()][self::DATA_SOURCE_DEFAULT]['samples_title'] = $this->samplesData->getSamplesTitle();
        $data[$model->getId()]['downloadable']['sample'] = $this->samplesData->getSamplesData();

        return $data;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $samplesPath = Composite::CHILDREN_PATH . '/' . Composite::CONTAINER_SAMPLES;
        $samplesContainer['arguments']['data']['config'] = [
            'additionalClasses' => 'admin__fieldset-section',
            'componentType' => Form\Fieldset::NAME,
            'label' => __('Samples'),
            'dataScope' => '',
            'visible' => $this->locator->getProduct()->getTypeId() === Type::TYPE_DOWNLOADABLE,
            'sortOrder' => 40,
        ];
        $samplesTitle['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => __('Title'),
            'dataScope' => 'product.samples_title',
            'scopeLabel' => $this->storeManager->isSingleStoreMode() ? '' : '[STORE VIEW]',
        ];
        // @codingStandardsIgnoreStart
        $informationSamples['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/html',
            'additionalClasses' => 'admin__fieldset-note',
            'content' => __('Alphanumeric, dash and underscore characters are recommended for filenames. Improper characters are replaced with \'_\'.'),
        ];
        // @codingStandardsIgnoreEnd

        $samplesContainer = $this->arrayManager->set(
            'children',
            $samplesContainer,
            [
                'samples_title' => $samplesTitle,
                'sample' => $this->getDynamicRows(),
                'information_samples' => $informationSamples,
            ]
        );

        return $this->arrayManager->set($samplesPath, $meta, $samplesContainer);
    }

    /**
     * Returns configuration for dynamic rows
     *
     * @return array
     */
    protected function getDynamicRows()
    {
        $dynamicRows['arguments']['data']['config'] = [
            'addButtonLabel' => __('Add Link'),
            'componentType' => DynamicRows::NAME,
            'itemTemplate' => 'record',
            'renderDefaultRecord' => false,
            'columnsHeader' => true,
            'additionalClasses' => 'admin__field-wide',
            'dataScope' => 'downloadable',
            'deleteProperty'=> 'is_delete',
            'deleteValue' => '1',
        ];

        return $this->arrayManager->set('children/record', $dynamicRows, $this->getRecord());
    }

    /**
     * Returns Record column configuration
     *
     * @return array
     */
    protected function getRecord()
    {
        $record['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'isTemplate' => true,
            'is_collection' => true,
            'component' => 'Magento_Ui/js/dynamic-rows/record',
            'dataScope' => '',
        ];
        $recordPosition['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'sort_order',
            'visible' => false,
        ];
        $recordActionDelete['arguments']['data']['config'] = [
            'label' => null,
            'componentType' => 'actionDelete',
            'fit' => true,
        ];

        return $this->arrayManager->set(
            'children',
            $record,
            [
                'container_sample_title' => $this->getTitleColumn(),
                'container_sample' => $this->getSampleColumn(),
                'position' => $recordPosition,
                'action_delete' => $recordActionDelete,
            ]
        );
    }

    /**
     * Returns Title column configuration
     *
     * @return array
     */
    protected function getTitleColumn()
    {
        $titleContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'showLabel' => false,
            'label' => __('Title'),
            'dataScope' => '',
            'sortOrder' => 10,
        ];
        $titleField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'title',
            'labelVisible' => false,
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/sample_title', $titleContainer, $titleField);
    }

    /**
     * Returns Sample column configuration
     *
     * @return array
     */
    protected function getSampleColumn()
    {
        $sampleContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('File'),
            'showLabel' => false,
            'dataScope' => '',
            'sortOrder' => 20,
        ];
        $sampleType['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'component' => 'Magento_Downloadable/js/components/upload-type-handler',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'type',
            'labelVisible' => false,
            'options' => $this->typeUpload->toOptionArray(),
            'typeFile' => 'sample_file',
            'typeUrl' => 'sample_url',
        ];
        $sampleUrl['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'sample_url',
            'placeholder' => 'URL',
            'labelVisible' => false,
            'validation' => [
                'required-entry' => true,
                'validate-url' => true,
            ],
        ];
        $sampleUploader['arguments']['data']['config'] = [
            'formElement' => 'fileUploader',
            'componentType' => 'fileUploader',
            'component' => 'Magento_Downloadable/js/components/file-uploader',
            'elementTmpl' => 'Magento_Downloadable/components/file-uploader',
            'fileInputName' => 'samples',
            'uploaderConfig' => [
                'url' => $this->urlBuilder->getUrl(
                    'adminhtml/downloadable_file/upload',
                    ['type' => 'samples', '_secure' => true]
                ),
            ],
            'dataScope' => 'file',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set(
            'children',
            $sampleContainer,
            [
                'sample_type' => $sampleType,
                'sample_url' => $sampleUrl,
                'sample_file' => $sampleUploader,
            ]
        );
    }
}
