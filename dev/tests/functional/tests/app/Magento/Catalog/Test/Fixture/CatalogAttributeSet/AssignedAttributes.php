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

namespace Magento\Catalog\Test\Fixture\CatalogAttributeSet;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Class AssignedAttributes
 *
 *  Data keys:
 *  - presets
 *  - attributes
 */
class AssignedAttributes implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params = [];

    /**
     * Names of assigned attributes
     *
     * @var array
     */
    protected $data = [];

    /**
     * Assigned attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['presets']) && is_string($data['presets'])) {
            $presets = explode(',', $data['presets']);
            foreach ($presets as $preset) {
                /** @var CatalogProductAttribute $attribute */
                $attribute = $fixtureFactory->createByCode('catalogProductAttribute', ['dataSet' => $preset]);
                $attribute->persist();

                $this->data[] = $attribute->getAttributeCode();
                $this->attributes[] = $attribute;
            }
        } elseif (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                /** @var CatalogProductAttribute $attribute */
                $this->data[] = $attribute->getAttributeCode();
                $this->attributes[] = $attribute;
            }
        } else {
            $this->data = $data;
        }
    }

    /**
     * Persist attribute
     *
     * @return void
     */
    public function persist()
    {
        //
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
     * Get Attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
