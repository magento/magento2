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

/**
 * Widget to display catalog link
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Widget;

use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Link extends \Magento\Framework\View\Element\Html\Link implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Entity model name which must be used to retrieve entity specific data.
     * @var null|\Magento\Catalog\Model\Resource\AbstractResource
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
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        UrlFinderInterface $urlFinder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlFinder = $urlFinder;
    }

    /**
     * Prepare url using passed id path and return it
     * or return false if path was not found in url rewrites.
     *
     * @throws \RuntimeException
     * @return string|false
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
        if (!$this->_anchorText && $this->_entityResource) {
            if (!$this->getData('label')) {
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
            } else {
                $this->_anchorText = $this->getData('label');
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
