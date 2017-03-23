<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\CatalogAttributeSet;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Create new Attribute Set via curl
 */
class Curl extends AbstractCurl implements CatalogAttributeSetInterface
{
    /**
     * Regex for finding attribute set id
     *
     * @var string
     */
    protected $attributeSetId = '`http.*?product_set\/delete\/id\/(\d*?)\/`';

    /**
     * Regex for finding attributes
     *
     * @var string
     */
    protected $attributes = '/buildCategoryTree\(this.root, ([^;]+\}\])\);/s';

    /**
     * Regex for finding attribute set name
     *
     * @var string
     */
    protected $attributeSetName = '#id="attribute_set_name".*?value="([\w\d]+)"#s';

    /**
     * Post request for creating Attribute Set
     *
     * @param FixtureInterface|null $fixture
     * @return array
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var CatalogAttributeSet $fixture */
        $response = $fixture->hasData('attribute_set_id')
            ? $this->getDefaultAttributeSet($fixture)
            : $this->createAttributeSet($fixture);

        $attributeSetId = ($fixture->hasData('attribute_set_id'))
            ? $fixture->getAttributeSetId()
            : $this->getData($this->attributeSetId, $response);

        $assignedAttributes = $fixture->hasData('assigned_attributes')
            ? $fixture->getDataFieldConfig('assigned_attributes')['source']->getAttributes()
            : [];
        $dataAttribute = $this->getDataAttributes($response);

        $lastAttribute = array_pop($dataAttribute['attributes']);

        foreach ($assignedAttributes as $key => $assignedAttribute) {
            $dataAttribute['attributes'][] = [
                $assignedAttribute->getAttributeId(),
                $dataAttribute['groups'][0][0],
                ($lastAttribute[2] + ($key + 1)),
                null,
            ];
        }

        $this->updateAttributeSet($attributeSetId, $dataAttribute);

        return ['attribute_set_id' => $attributeSetId];
    }

    /**
     * Create Attribute Set
     *
     * @param CatalogAttributeSet $fixture
     * @return string
     */
    protected function createAttributeSet(CatalogAttributeSet $fixture)
    {
        $data = $fixture->getData();
        if (!isset($data['gotoEdit'])) {
            $data['gotoEdit'] = 1;
        }

        $data['skeleton_set'] = $fixture->getDataFieldConfig('skeleton_set')['source']->getAttributeSet()
            ->getAttributeSetId();

        $url = $_ENV['app_backend_url'] . 'catalog/product_set/save/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        return $response;
    }

    /**
     * Get Default Attribute Set page with curl
     *
     * @param CatalogAttributeSet $fixture
     * @return string
     */
    protected function getDefaultAttributeSet(CatalogAttributeSet $fixture)
    {
        $url = $_ENV['app_backend_url'] . 'catalog/product_set/edit/id/' . $fixture->getAttributeSetId() . '/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, [], CurlInterface::GET);
        $response = $curl->read();
        $curl->close();

        return $response;
    }

    /**
     * Update Attribute Set
     *
     * @param int $attributeSetId
     * @param array $dataAttribute
     * @return void
     */
    protected function updateAttributeSet($attributeSetId, array $dataAttribute)
    {
        $data = ['data' => json_encode($dataAttribute)];
        $url = $_ENV['app_backend_url'] . 'catalog/product_set/save/id/' . $attributeSetId . '/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $curl->read();
        $curl->close();
    }

    /**
     * Get data attributes for curl
     *
     * @param string $response
     * @return array
     */
    protected function getDataAttributes($response)
    {
        $attributes = $this->getData($this->attributes, $response, true);
        $dataAttribute = [];

        $index = 1;
        foreach ($attributes as $key => $parentAttributes) {
            $dataAttribute['groups'][$key][] = $parentAttributes['id'];
            $dataAttribute['groups'][$key][] = $parentAttributes['text'];
            $dataAttribute['groups'][$key][] = $key + 1;

            if (isset($parentAttributes['children'])) {
                foreach ($parentAttributes['children'] as $attribute) {
                    $dataAttribute['attributes'][$index][] = $attribute['id'];
                    $dataAttribute['attributes'][$index][] = $parentAttributes['id'];
                    $dataAttribute['attributes'][$index][] = $index;
                    $dataAttribute['attributes'][$index][] = $attribute['entity_id'];
                    $index++;
                }
            }
        }
        $dataAttribute['not_attributes'] = [];
        $dataAttribute['removeGroups'] = [];
        $dataAttribute['attribute_set_name'] = $this->getData($this->attributeSetName, $response);

        return $dataAttribute;
    }

    /**
     * Select data from response by regular expression
     *
     * @param string $regularExpression
     * @param string $response
     * @param bool $isJson
     * @return mixed
     * @throws \Exception
     */
    protected function getData($regularExpression, $response, $isJson = false)
    {
        preg_match($regularExpression, $response, $matches);
        if (!isset($matches[1])) {
            throw new \Exception("Can't find data in response by regular expression \"{$regularExpression}\".");
        }

        return $isJson ? json_decode($matches[1], true) : $matches[1];
    }
}
