<?php
 
namespace Webiators\CustomChanges\Plugin;
use Magento\Catalog\Model\Product;
 
class HideButton
{
 public function aroundIsSalable(Product $product,\Closure $result)
 {
 return 0;
 }
}