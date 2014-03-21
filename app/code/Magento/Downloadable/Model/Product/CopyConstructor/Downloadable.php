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
namespace Magento\Downloadable\Model\Product\CopyConstructor;

class Downloadable implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $encoder;

    /**
     * @param \Magento\Core\Helper\Data $encoder
     */
    public function __construct(\Magento\Core\Helper\Data $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * Duplicating downloadable product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        if ($product->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            //do nothing if not downloadable
            return;
        }
        $data = array();
        /** @var \Magento\Downloadable\Model\Product\Type $type */
        $type = $product->getTypeInstance();
        foreach ($type->getLinks($product) as $link) {
            /* @var \Magento\Downloadable\Model\Link $link */
            $linkData = $link->getData();
            $data['link'][] = array(
                'is_delete' => false,
                'link_id' => null,
                'title' => $linkData['title'],
                'is_shareable' => $linkData['is_shareable'],
                'sample' => array(
                    'type' => $linkData['sample_type'],
                    'url' => $linkData['sample_url'],
                    'file' => $this->encoder->jsonEncode(
                        array(
                            array(
                                'file' => $linkData['sample_file'],
                                'name' => $linkData['sample_file'],
                                'size' => 0,
                                'status' => null
                            )
                        )
                    )
                ),
                'file' => $this->encoder->jsonEncode(
                    array(
                        array(
                            'file' => $linkData['link_file'],
                            'name' => $linkData['link_file'],
                            'size' => 0,
                            'status' => null
                        )
                    )
                ),
                'type' => $linkData['link_type'],
                'link_url' => $linkData['link_url'],
                'sort_order' => $linkData['sort_order'],
                'number_of_downloads' => $linkData['number_of_downloads'],
                'price' => $linkData['price']
            );
        }

        /** @var \Magento\Downloadable\Model\Sample $sample */
        foreach ($type->getSamples($product) as $sample) {
            $sampleData = $sample->getData();
            $data['sample'][] = array(
                'is_delete' => false,
                'sample_id' => null,
                'title' => $sampleData['title'],
                'type' => $sampleData['sample_type'],
                'file' => $this->encoder->jsonEncode(
                    array(
                        array(
                            'file' => $sampleData['sample_file'],
                            'name' => $sampleData['sample_file'],
                            'size' => 0,
                            'status' => null
                        )
                    )
                ),
                'sample_url' => $sampleData['sample_url'],
                'sort_order' => $sampleData['sort_order']
            );
        }
        $duplicate->setDownloadableData($data);
    }
}
