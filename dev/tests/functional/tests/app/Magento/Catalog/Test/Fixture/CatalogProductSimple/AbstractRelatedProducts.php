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

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class AbstractRelatedProducts
 * Base class create related products
 */
class AbstractRelatedProducts implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Data of the created products
     *
     * @var array
     */
    protected $data = [];

    /**
     * Products fixture
     *
     * @var array
     */
    protected $products = [];

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;

        if (isset($data['presets'])) {
            $presets = array_map('trim', explode(',', $data['presets']));
            foreach ($presets as $preset) {
                list($fixtureCode, $dataSet) = explode('::', $preset);

                /** @var CatalogProductSimple $product */
                $product = $fixtureFactory->createByCode($fixtureCode, ['dataSet' => $dataSet]);
                if (!$product->hasData('id')) {
                    $product->persist();
                }

                $this->products[] = $product;
                $this->data[] = [
                    'entity_id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku()
                ];
            }
        }

        if (isset($data['data'])) {
            $this->data = array_replace_recursive($this->data, $data['data']);
        }
    }

    /**
     * Persist related products
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string|null $key
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return related products
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
