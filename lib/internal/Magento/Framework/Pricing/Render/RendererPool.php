<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * @api
 */
class RendererPool extends AbstractBlock
{
    /**
     * Default price group type
     */
    const DEFAULT_PRICE_GROUP_TYPE = 'default';

    /**
     * Default price renderer
     */
    const PRICE_RENDERER_DEFAULT = \Magento\Framework\Pricing\Render\PriceBox::class;

    /**
     * Default amount renderer
     */
    const AMOUNT_RENDERER_DEFAULT = \Magento\Framework\Pricing\Render\Amount::class;

    /**
     * Create amount renderer
     *
     * @param string $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $data
     * @throws \InvalidArgumentException
     * @return PriceBoxRenderInterface
     */
    public function createPriceRender(
        $priceCode,
        SaleableInterface $saleableItem,
        array $data = []
    ) {
        $type = $saleableItem->getTypeId();

        // implement class resolving fallback
        $pattern = [
            $type . '/prices/' . $priceCode . '/render_class',
            $type . '/default_render_class',
            'default/prices/' . $priceCode . '/render_class',
            'default/default_render_class',
        ];
        $renderClassName = $this->findDataByPattern($pattern);
        if (!$renderClassName) {
            throw new \InvalidArgumentException(
                'Class name for price code "' . $priceCode . '" not registered'
            );
        }

        $price = $saleableItem->getPriceInfo()->getPrice($priceCode);
        if (!$price) {
            throw new \InvalidArgumentException(
                'Price model for price code "' . $priceCode . '" not registered'
            );
        }

        $arguments['data'] = $data;
        $arguments['rendererPool'] = $this;
        $arguments['price'] = $price;
        $arguments['saleableItem'] = $saleableItem;

        /** @var \Magento\Framework\View\Element\Template $renderBlock */
        $renderBlock = $this->getLayout()->createBlock($renderClassName, '', $arguments);
        if (!$renderBlock instanceof PriceBoxRenderInterface) {
            throw new \InvalidArgumentException(
                'Block "' . $renderClassName
                . '" must implement \Magento\Framework\Pricing\Render\PriceBoxRenderInterface'
            );
        }
        $renderBlock->setTemplate($this->getRenderBlockTemplate($type, $priceCode));
        return $renderBlock;
    }

    /**
     * Create amount renderer
     *
     * @param AmountInterface $amount
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param array $data
     * @return AmountRenderInterface
     * @throws \InvalidArgumentException
     */
    public function createAmountRender(
        AmountInterface $amount,
        SaleableInterface $saleableItem = null,
        PriceInterface $price = null,
        array $data = []
    ) {
        $type = self::DEFAULT_PRICE_GROUP_TYPE;
        if ($saleableItem) {
            $type = $saleableItem->getTypeId();
        }

        $priceCode = null;
        $renderClassName = self::AMOUNT_RENDERER_DEFAULT;

        if ($price) {
            $priceCode = $price->getPriceCode();
            // implement class resolving fallback
            $pattern = [
                $type . '/prices/' . $priceCode . '/amount_render_class',
                $type . '/default_amount_render_class',
                'default/prices/' . $priceCode . '/amount_render_class',
                'default/default_amount_render_class',
            ];
            $renderClassName = $this->findDataByPattern($pattern);
            if (!$renderClassName) {
                throw new \InvalidArgumentException(
                    'There is no amount render class for price code "' . $priceCode . '"'
                );
            }
        }

        $arguments['data'] = $data;
        $arguments['rendererPool'] = $this;
        $arguments['amount'] = $amount;

        if ($saleableItem) {
            $arguments['saleableItem'] = $saleableItem;
            if ($price) {
                $arguments['price'] = $price;
            }
        }

        /** @var \Magento\Framework\View\Element\Template $amountBlock */
        $amountBlock = $this->getLayout()->createBlock($renderClassName, '', $arguments);
        if (!$amountBlock instanceof AmountRenderInterface) {
            throw new \InvalidArgumentException(
                'Block "' . $renderClassName
                . '" must implement \Magento\Framework\Pricing\Render\AmountRenderInterface'
            );
        }
        $amountBlock->setTemplate($this->getAmountRenderBlockTemplate($type, $priceCode));
        return $amountBlock;
    }

    /**
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @return array
     */
    public function getAdjustmentRenders(SaleableInterface $saleableItem = null, PriceInterface $price = null)
    {
        $itemType = null === $saleableItem ? 'default' : $saleableItem->getTypeId();
        $priceType = null === $price ? 'default' : $price->getPriceCode();

        $fallbackPattern = [
            "{$itemType}/adjustments/{$priceType}",
            "{$itemType}/adjustments/default",
            "default/adjustments/{$priceType}",
            "default/adjustments/default",
        ];
        $renders = $this->findDataByPattern($fallbackPattern);
        if ($renders) {
            foreach ($renders as $code => $configuration) {
                /** @var \Magento\Framework\View\Element\Template $render */
                $render = $this->getLayout()->createBlock($configuration['adjustment_render_class']);
                $render->setTemplate($configuration['adjustment_render_template']);
                $renders[$code] = $render;
            }
        }

        return $renders;
    }

    /**
     * @param string $type
     * @param string $priceCode
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getAmountRenderBlockTemplate($type, $priceCode)
    {
        $pattern = [
            $type . '/prices/' . $priceCode . '/amount_render_template',
            $type . '/default_amount_render_template',
            'default/prices/' . $priceCode . '/amount_render_template',
            'default/default_amount_render_template',
        ];
        $template = $this->findDataByPattern($pattern);
        if (!$template) {
            throw new \InvalidArgumentException(
                'For type "' . $type . '" amount render block not configured'
            );
        }
        return $template;
    }

    /**
     * @param string $type
     * @param string $priceCode
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getRenderBlockTemplate($type, $priceCode)
    {
        $pattern = [
            $type . '/prices/' . $priceCode . '/render_template',
            $type . '/default_render_template',
            'default/prices/' . $priceCode . '/render_template',
            'default/default_render_template',
        ];
        $template = $this->findDataByPattern($pattern);
        if (!$template) {
            throw new \InvalidArgumentException(
                'Price code "' . $priceCode . '" render block not configured'
            );
        }
        return $template;
    }

    /**
     * @param array $pattern
     * @return null|string
     */
    protected function findDataByPattern(array $pattern)
    {
        $data = null;
        foreach ($pattern as $key) {
            $data = $this->getData($key);
            if ($data) {
                break;
            }
        }
        return $data;
    }
}
