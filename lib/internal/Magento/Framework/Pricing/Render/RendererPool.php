<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * RenderPool
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
     * @var array
     */
    private $renderClassNames;
    
    /**
     * @var array
     */
    private $renderBlockTemplates;
    
    /**
     * @var array
     */
    private $amountRenderClassNames;
    
    /**
     * @var array
     */
    private $amountRenderBlockTemplates;
    
    /**
     * @var array
     */
    private $renders;

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
        if(isset($this->renderClassNames[$type][$priceCode])){
            $renderClassName=$this->renderClassNames[$type][$priceCode];            
        } else{
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
        $this->renderClassNames[$type][$priceCode]=$renderClassName;
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
                'Block "' . $renderClassName . '" must implement \Magento\Framework\Pricing\Render\PriceBoxRenderInterface'
            );
        }
        if(isset($this->renderBlockTemplates[$type][$priceCode])){
            $renderBlockTemplate=  $this->renderBlockTemplates[$type][$priceCode];
        } else{
            $renderBlockTemplate=$this->getRenderBlockTemplate($type, $priceCode);
            $this->renderBlockTemplates[$type][$priceCode]=$renderBlockTemplate;           
        }
        $renderBlock->setTemplate($renderBlockTemplate);
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
            if(isset($this->amountRenderClassNames[$type][$priceCode])){
                $renderClassName=$this->amountRenderClassNames[$type][$priceCode];
            } else {
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
            $this->amountRenderClassNames[$type][$priceCode]=$renderClassName;
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
                'Block "' . $renderClassName . '" must implement \Magento\Framework\Pricing\Render\AmountRenderInterface'
            );
        }
        if(isset($this->amountRenderBlockTemplates[$type][$priceCode])){
            $amountRenderBlockTemplate=$this->amountRenderBlockTemplates[$type][$priceCode];
        } else {
            $amountRenderBlockTemplate=$this->getAmountRenderBlockTemplate($type, $priceCode);
            $this->amountRenderBlockTemplates[$type][$priceCode]=$amountRenderBlockTemplate;
        }
        $amountBlock->setTemplate($amountRenderBlockTemplate);
        return $amountBlock;
    }

    /**
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @return array
     */
    public function getAdjustmentRenders(SaleableInterface $saleableItem = null, PriceInterface $price = null)
    {
        $itemType = is_null($saleableItem) ? 'default' : $saleableItem->getTypeId();
        $priceType = is_null($price) ? 'default' : $price->getPriceCode();
        if(isset($this->renders[$itemType][$priceType])){
           return $this->renders[$itemType][$priceType]; 
        } 
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
        $this->renders[$itemType][$priceType]=$renders;
        
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
