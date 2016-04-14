<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data;

use \Magento\Framework\Escaper;
use Magento\Downloadable\Model\Sample as SampleModel;
use Magento\Downloadable\Model\Product\Type;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\UrlInterface;
use Magento\Downloadable\Api\Data\SampleInterface;

/**
 * Class Samples
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Samples
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var SampleModel
     */
    protected $sampleModel;

    /**
     * @var DownloadableFile
     */
    protected $downloadableFile;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Escaper $escaper
     * @param LocatorInterface $locator
     * @param ScopeConfigInterface $scopeConfig
     * @param SampleModel $sampleModel
     * @param DownloadableFile $downloadableFile
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Escaper $escaper,
        LocatorInterface $locator,
        ScopeConfigInterface $scopeConfig,
        SampleModel $sampleModel,
        DownloadableFile $downloadableFile,
        UrlInterface $urlBuilder
    ) {
        $this->escaper = $escaper;
        $this->locator = $locator;
        $this->scopeConfig = $scopeConfig;
        $this->sampleModel = $sampleModel;
        $this->downloadableFile = $downloadableFile;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve Default samples title
     *
     * @return string
     */
    public function getSamplesTitle()
    {
        return $this->locator->getProduct()->getId()
        && $this->locator->getProduct()->getTypeId() == Type::TYPE_DOWNLOADABLE
            ? $this->locator->getProduct()->getSamplesTitle()
            : $this->scopeConfig->getValue(
                \Magento\Downloadable\Model\Sample::XML_PATH_SAMPLES_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Get Samples data
     *
     * @return array
     */
    public function getSamplesData()
    {
        $samplesData = [];
        if ($this->locator->getProduct()->getTypeId() !== Type::TYPE_DOWNLOADABLE) {
            return $samplesData;
        }

        $samples = $this->locator->getProduct()->getTypeInstance()->getSamples($this->locator->getProduct());
        /** @var SampleInterface $sample */
        foreach ($samples as $sample) {
            $sampleData = [];
            $sampleData['sample_id'] = $sample->getId() ?: 0;
            $sampleData['title'] = $this->escaper->escapeHtml($sample->getTitle());
            $sampleData['sample_url'] = $sample->getSampleUrl();
            $sampleData['type'] = $sample->getSampleType();
            $sampleData['sort_order'] = $sample->getSortOrder();

            if ($this->locator->getProduct()->getStoreId()) {
                $sampleData['use_default_title'] = $sample->getStoreTitle() ? '0' : '1';
            }

            $sampleData = $this->addSampleFile($sampleData, $sample);

            $samplesData[] = $sampleData;
        }

        return $samplesData;
    }

    /**
     * Add Sample File info into $sampleData
     *
     * @param array $sampleData
     * @param SampleInterface $sample
     * @return array
     */
    protected function addSampleFile(array $sampleData, SampleInterface $sample)
    {
        $sampleFile = $sample->getSampleFile();
        if ($sampleFile) {
            $file = $this->downloadableFile->getFilePath($this->sampleModel->getBasePath(), $sampleFile);
            if ($this->downloadableFile->ensureFileInFilesystem($file)) {
                $sampleData['file'][0] = [
                    'file' => $sampleFile,
                    'name' => $this->downloadableFile->getFileFromPathFile($sampleFile),
                    'size' => $this->downloadableFile->getFileSize($file),
                    'status' => 'old',
                    'url' => $this->urlBuilder->addSessionParam()->getUrl(
                        'adminhtml/downloadable_product_edit/sample',
                        ['id' => $sample->getId(), '_secure' => true]
                    ),
                ];
            }
        }

        return $sampleData;
    }
}
