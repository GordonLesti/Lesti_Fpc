<?php
/**
 * Lesti_Fpc
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * http://opensource.org/licenses/OSL-3.0
 *
 * @package      Lesti_Fpc
 * @copyright    Copyright (c) 2013 Gordon Lesti (http://www.gordonlesti.com)
 * @author       Gordon Lesti <info@gordonlesti.com>
 * @license      http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * Class Lesti_Fpc_Block_Catalog_Product_View_Ajax
 */
class Lesti_Fpc_Block_Catalog_Product_View_Ajax extends Mage_Core_Block_Template
{
    /**
     * @return bool|string
     */
    public function getAjaxUrl()
    {
        $fpc = Mage::getSingleton('fpc/fpc');
        $id = $this->_getProductId();
        if ($fpc->isActive() &&
            in_array('catalog_product_view', Mage::helper('fpc')->getCacheableActions()) &&
            Mage::helper('fpc/block')->useRecentlyViewedProducts() &&
            $id
        ) {
            return $this->getUrl('fpc/catalog_product/view', array('id' => $id));
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function _getProductId()
    {
        $product = Mage::registry('current_product');
        if ($product) {
            return $product->getId();
        }
        return false;
    }

}