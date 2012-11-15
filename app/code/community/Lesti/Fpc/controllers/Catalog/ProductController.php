<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 08.11.12
 * Time: 14:01
 * To change this template use File | Settings | File Templates.
 */
class Lesti_Fpc_Catalog_ProductController extends Mage_Core_Controller_Front_Action
{

    public function viewAction()
    {
        $productId = (int)Mage::app()->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->getId()) {
            Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
        }
    }

}