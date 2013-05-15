<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 01.11.12
 * Time: 21:56
 * To change this template use File | Settings | File Templates.
 */
class Lesti_Fpc_Helper_Block extends Mage_Core_Helper_Abstract
{
    const DYNAMIC_BLOCKS_XML_PATH = 'system/fpc/dynamic_blocks';
    const USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH = 'system/fpc/use_recently_viewed_products';

    public function getDynamicBlocks()
    {
        $blocks = Mage::getStoreConfig(self::DYNAMIC_BLOCKS_XML_PATH);
        $blocks = array_map('trim', explode(',', $blocks));
        return $blocks;
    }

    public function getPlaceholderHtml($blockName)
    {
        return '<!-- fpc ' . sha1($blockName) . ' -->';
    }

    public function getKey($blockName)
    {
        return sha1($blockName) . '_block';
    }

    public function useRecentlyViewedProducts()
    {
        return Mage::getStoreConfig(self::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH);
    }

    public function getCacheTags($block)
    {
        $cacheTags = array();
        $blockName = $block->getNameInLayout();
        if ($blockName == 'product_list') {
            $cacheTags[] = sha1('product');
            foreach($block->getLoadedProductCollection() as $product) {
                $cacheTags[] = sha1('product_' . $product->getId());
            }
        } else if (get_class($block) == get_class(Mage::getBlockSingleton('cms/block'))) {
            $cacheTags[] = sha1('cmsblock');
            $cacheTags[] = sha1('cmsblock_' . $block->getBlockId());
        }
        return $cacheTags;
    }

}