<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data;

use \Magento\Framework\Escaper;
use Magento\Downloadable\Model\Product\Type;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\UrlInterface;
use Magento\Downloadable\Model\Link as LinkModel;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class Links
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Links
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
     * @var DownloadableFile
     */
    protected $downloadableFile;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LinkModel
     */
    protected $linkModel;

    /**
     * @param Escaper $escaper
     * @param LocatorInterface $locator
     * @param ScopeConfigInterface $scopeConfig
     * @param DownloadableFile $downloadableFile
     * @param UrlInterface $urlBuilder
     * @param LinkModel $linkModel
     */
    public function __construct(
        Escaper $escaper,
        LocatorInterface $locator,
        ScopeConfigInterface $scopeConfig,
        DownloadableFile $downloadableFile,
        UrlInterface $urlBuilder,
        LinkModel $linkModel
    ) {
        $this->escaper = $escaper;
        $this->locator = $locator;
        $this->scopeConfig = $scopeConfig;
        $this->downloadableFile = $downloadableFile;
        $this->urlBuilder = $urlBuilder;
        $this->linkModel = $linkModel;
    }

    /**
     * Retrieve default links title
     *
     * @return string
     */
    public function getLinksTitle()
    {
        return $this->locator->getProduct()->getId() &&
        $this->locator->getProduct()->getTypeId() == Type::TYPE_DOWNLOADABLE
            ? $this->locator->getProduct()->getLinksTitle()
            : $this->scopeConfig->getValue(
                \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Get Links can be purchased separately value for current product
     *
     * @return bool
     */
    public function isProductLinksCanBePurchasedSeparately()
    {
        return (bool) $this->locator->getProduct()->getData('links_purchased_separately');
    }

    /**
     * Get Links data
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return array
     */
    public function getLinksData()
    {
        $linksData = [];
        if ($this->locator->getProduct()->getTypeId() !== Type::TYPE_DOWNLOADABLE) {
            return $linksData;
        }

        $links = $this->locator->getProduct()->getTypeInstance()->getLinks($this->locator->getProduct());
        /** @var LinkInterface $link */
        foreach ($links as $link) {
            $linkData = [];
            $linkData['link_id'] = $link->getId();
            $linkData['title'] = $this->escaper->escapeHtml($link->getTitle());
            $linkData['price'] = $this->getPriceValue($link->getPrice());
            $linkData['number_of_downloads'] = $link->getNumberOfDownloads();
            $linkData['is_shareable'] = $link->getIsShareable();
            $linkData['link_url'] = $link->getLinkUrl();
            $linkData['type'] = $link->getLinkType();
            $linkData['sample']['url'] = $link->getSampleUrl();
            $linkData['sample']['type'] = $link->getSampleType();
            $linkData['sort_order'] = $link->getSortOrder();
            $linkData['is_unlimited'] = $linkData['number_of_downloads'] ? '0' : '1';

            if ($this->locator->getProduct()->getStoreId()) {
                $linkData['use_default_price'] = $link->getWebsitePrice() ? '0' : '1';
                $linkData['use_default_title'] = $link->getStoreTitle() ? '0' : '1';
            }

            $linkData = $this->addLinkFile($linkData, $link);
            $linkData = $this->addSampleFile($linkData, $link);

            $linksData[] = $linkData;
        }

        return $linksData;
    }

    /**
     * Add Sample File info into $linkData
     *
     * @param array $linkData
     * @param LinkInterface $link
     * @return array
     */
    protected function addSampleFile(array $linkData, LinkInterface $link)
    {
        $sampleFile = $link->getSampleFile();
        if ($sampleFile) {
            $file = $this->downloadableFile->getFilePath($this->linkModel->getBaseSamplePath(), $sampleFile);
            if ($this->isLinkFileValid($file)) {
                $linkData['sample']['file'][0] = [
                    'file' => $sampleFile,
                    'name' => $this->downloadableFile->getFileFromPathFile($sampleFile),
                    'size' => $this->downloadableFile->getFileSize($file),
                    'status' => 'old',
                    'url' => $this->urlBuilder->addSessionParam()->getUrl(
                        'adminhtml/downloadable_product_edit/link',
                        ['id' => $link->getId(), 'type' => 'sample', '_secure' => true]
                    ),
                ];
            }
        }

        return $linkData;
    }

    /**
     * Add Link File info into $linkData
     *
     * @param array $linkData
     * @param LinkInterface $link
     * @return array
     */
    protected function addLinkFile(array $linkData, LinkInterface $link)
    {
        $linkFile = $link->getLinkFile();
        if ($linkFile) {
            $file = $this->downloadableFile->getFilePath($this->linkModel->getBasePath(), $linkFile);
            if ($this->isLinkFileValid($file)) {
                $linkData['file'][0] = [
                    'file' => $linkFile,
                    'name' => $this->downloadableFile->getFileFromPathFile($linkFile),
                    'size' => $this->downloadableFile->getFileSize($file),
                    'status' => 'old',
                    'url' => $this->urlBuilder->addSessionParam()->getUrl(
                        'adminhtml/downloadable_product_edit/link',
                        ['id' => $link->getId(), 'type' => 'link', '_secure' => true]
                    ),
                ];
            }
        }

        return $linkData;
    }

    /**
     * Check that Links File or Sample is valid.
     *
     * @param string $file
     * @return bool
     */
    private function isLinkFileValid(string $file): bool
    {
        try {
            return $this->downloadableFile->ensureFileInFilesystem($file);
        } catch (ValidatorException $e) {
            return false;
        }
    }

    /**
     * Return formatted price with two digits after decimal point
     *
     * @param float $value
     * @return string
     */
    public function getPriceValue($value)
    {
        return number_format($value, 2, null, '');
    }
}
