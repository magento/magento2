<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Widget to display catalog link
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Widget;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class \Magento\Catalog\Block\Widget\Link
 *
 */
class Link extends \Magento\Framework\View\Element\Html\Link implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Entity model name which must be used to retrieve entity specific data.
     * @var null|\Magento\Catalog\Model\ResourceModel\AbstractResource
     */
    protected $_entityResource = null;

    /**
     * Prepared href attribute
     *
     * @var string
     */
    protected $_href;

    /**
     * Prepared anchor text
     *
     * @var string
     */
    protected $_anchorText;

    /**
     * Url finder for category
     *
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Catalog\Model\ResourceModel\AbstractResource $entityResource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        UrlFinderInterface $urlFinder,
        \Magento\Catalog\Model\ResourceModel\AbstractResource $entityResource = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlFinder = $urlFinder;
        $this->_entityResource = $entityResource;
    }

    /**
     * Prepare url using passed id path and return it
     * or return false if path was not found in url rewrites.
     *
     * @throws \RuntimeException
     * @return string|false
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getHref()
    {
        if ($this->_href === null) {
            if (!$this->getData('id_path')) {
                throw new \RuntimeException('Parameter id_path is not set.');
            }
            $rewriteData = $this->parseIdPath($this->getData('id_path'));

            $href = false;
            $store = $this->hasStoreId() ? $this->_storeManager->getStore($this->getStoreId())
                : $this->_storeManager->getStore();
            $filterData = [
                UrlRewrite::ENTITY_ID => $rewriteData[1],
                UrlRewrite::ENTITY_TYPE => $rewriteData[0],
                UrlRewrite::STORE_ID => $store->getId(),
            ];
            if (!empty($rewriteData[2]) && $rewriteData[0] == ProductUrlRewriteGenerator::ENTITY_TYPE) {
                $filterData[UrlRewrite::METADATA]['category_id'] = $rewriteData[2];
            }
            $rewrite = $this->urlFinder->findOneByData($filterData);

            if ($rewrite) {
                $href = $store->getUrl('', ['_direct' => $rewrite->getRequestPath()]);

                if (strpos($href, '___store') === false) {
                    $href .= (strpos($href, '?') === false ? '?' : '&') . '___store=' . $store->getCode();
                }
            }
            $this->_href = $href;
        }
        return $this->_href;
    }

    /**
     * Parse id_path
     *
     * @param string $idPath
     * @throws \RuntimeException
     * @return array
     */
    protected function parseIdPath($idPath)
    {
        $rewriteData = explode('/', $idPath);

        if (!isset($rewriteData[0]) || !isset($rewriteData[1])) {
            throw new \RuntimeException('Wrong id_path structure.');
        }
        return $rewriteData;
    }

    /**
     * Prepare label using passed text as parameter.
     * If anchor text was not specified get entity name from DB.
     *
     * @return string
     */
    public function getLabel()
    {
        if (!$this->_anchorText) {
            if ($this->getData('anchor_text')) {
                $this->_anchorText = $this->getData('anchor_text');
            } elseif ($this->_entityResource) {
                $idPath = explode('/', $this->_getData('id_path'));
                if (isset($idPath[1])) {
                    $id = $idPath[1];
                    if ($id) {
                        $this->_anchorText = $this->_entityResource->getAttributeRawValue(
                            $id,
                            'name',
                            $this->_storeManager->getStore()
                        );
                    }
                }
            }
        }

        return $this->_anchorText;
    }

    /**
     * Render block HTML
     * or return empty string if url can't be prepared
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getHref()) {
            return parent::_toHtml();
        }
        return '';
    }
}
