<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 08.11.12
 * Time: 13:44
 * To change this template use File | Settings | File Templates.
 */
class Lesti_Fpc_Block_Catalog_Product_View_Ajax extends Mage_Core_Block_Template
{

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

    protected function _getProductId()
    {
        $product = Mage::registry('current_product');
        if ($product) {
            return $product->getId();
        }
        return false;
    }

}