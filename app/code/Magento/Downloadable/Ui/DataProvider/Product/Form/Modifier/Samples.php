<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Source\TypeUpload;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\DynamicRows;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;

/**
 * Class adds a grid with samples
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class Samples extends AbstractModifier
{
    /**
     * @var LocatorInterface
     * @since 2.1.0
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @var ArrayManager
     * @since 2.1.0
     */
    protected $arrayManager;

    /**
     * @var TypeUpload
     * @since 2.1.0
     */
    protected $typeUpload;

    /**
     * @var Data\Samples
     * @since 2.1.0
     */
    protected $samplesData;

    /**
     * @var UrlInterface
     * @since 2.1.0
     */
    protected $urlBuilder;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param TypeUpload $typeUpload
     * @param Data\Samples $samplesData
     * @since 2.1.0
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
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();

        $data[$model->getId()][self::DATA_SOURCE_DEFAULT]['samples_title'] = $this->samplesData->getSamplesTitle();
        $data[$model->getId()]['downloadable']['sample'] = $this->samplesData->getSamplesData();

        return $data;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.1.0
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
     * @return array
     * @since 2.1.0
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
     * @return array
     * @since 2.1.0
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
     * @return array
     * @since 2.1.0
     */
    protected function getTitleColumn()
    {
        $titleContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Title'),
            'dataScope' => '',
        ];
        $titleField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'title',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/sample_title', $titleContainer, $titleField);
    }

    /**
     * @return array
     * @since 2.1.0
     */
    protected function getSampleColumn()
    {
        $sampleContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('File'),
            'dataScope' => '',
        ];
        $sampleType['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'component' => 'Magento_Downloadable/js/components/upload-type-handler',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'type',
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
                'url' => $this->urlBuilder->addSessionParam()->getUrl(
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
