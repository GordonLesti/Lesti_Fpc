<?php
/**
 * Lesti_Fpc (http:gordonlesti.com/lestifpc)
 *
 * PHP version 5
 *
 * @link      https://github.com/GordonLesti/Lesti_Fpc
 * @package   Lesti_Fpc
 * @author    Gordon Lesti <info@gordonlesti.com>
 * @copyright Copyright (c) 2013-2014 Gordon Lesti (http://gordonlesti.com)
 * @license   http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * Class Lesti_Fpc_Helper_Block
 */
class Lesti_Fpc_Helper_Block extends Mage_Core_Helper_Abstract
{
    const DYNAMIC_BLOCKS_XML_PATH = 'system/fpc/dynamic_blocks';
    const LAZY_BLOCKS_XML_PATH = 'system/fpc/lazy_blocks';
    const LAZY_BLOCKS_VALID_SESSION_PARAM = 'fpc_lazy_blocks_valid';
    const USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH =
        'system/fpc/use_recently_viewed_products';

    /**
     * @return array
     */
    public function getDynamicBlocks()
    {
        $blocks = Mage::getStoreConfig(self::DYNAMIC_BLOCKS_XML_PATH);

        if ($blocks) {
            return array_unique(array_map('trim', explode(',', $blocks)));
        }

        return array();
    }

    /**
     * @return array
     */
    public function getLazyBlocks()
    {
        $blocks = Mage::getStoreConfig(self::LAZY_BLOCKS_XML_PATH);

        if ($blocks) {
            return array_unique(array_map('trim', explode(',', $blocks)));
        }

        return array();
    }

    /**
     * @return bool
     */
    public function areLazyBlocksValid()
    {
        $hash = $this->_getLazyBlocksValidHash();
        $session = Mage::getSingleton('customer/session');
        $sessionHash = $session->getData(self::LAZY_BLOCKS_VALID_SESSION_PARAM);
        if ($sessionHash === false || $hash != $sessionHash) {
            $session->setData(self::LAZY_BLOCKS_VALID_SESSION_PARAM, $hash);
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    protected function _getLazyBlocksValidHash()
    {
        $params = array();
        $request = Mage::app()->getRequest();
        $params['host'] = $request->getServer('HTTP_HOST');
        $params['port'] = $request->getServer('SERVER_PORT');
        // store
        $storeCode = Mage::app()->getStore()->getCode();
        if ($storeCode) {
            $params['store'] = $storeCode;
        }
        // currency
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        if ($currencyCode) {
            $params['currency'] = $currencyCode;
        }
        $customerSession = Mage::getSingleton('customer/session');
        $params['customer_group_id'] = $customerSession->getCustomerGroupId();
        $design = Mage::getDesign();
        $params['design'] = $design->getPackageName().'_'.
            $design->getTheme('template');

        $params['blocks'] = implode(',', $this->getLazyBlocks());

        return sha1(serialize($params));
    }

    /**
     * @param $blockName
     * @return string
     */
    public function getPlaceholderHtml($blockName)
    {
        return '<!-- fpc ' . sha1($blockName) . ' -->';
    }

    /**
     * @param $blockName
     * @return string
     */
    public function getKey($blockName)
    {
        return sha1($blockName) . '_block';
    }

    /**
     * @return mixed
     */
    public function useRecentlyViewedProducts()
    {
        return (bool)Mage::getStoreConfig(
            self::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH
        );
    }

    /**
     * @param $block
     * @return array
     */
    public function getCacheTags($block)
    {
        $cacheTags = array();
        $blockName = $block->getNameInLayout();
        if ($blockName == 'product_list') {
            $cacheTags[] = sha1('product');
            foreach ($block->getLoadedProductCollection() as $product) {
                $cacheTags[] = sha1('product_' . $product->getId());
            }
        } else if ($block instanceof Mage_Cms_Block_Block ||
        is_subclass_of($block, 'Mage_Cms_Block_Block')) {
            $cacheTags[] = sha1('cmsblock');
            $cacheTags[] = sha1('cmsblock_' . $block->getBlockId());
        }
        return $cacheTags;
    }

}
