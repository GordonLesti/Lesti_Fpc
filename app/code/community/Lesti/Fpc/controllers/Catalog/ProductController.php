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
 * Class Lesti_Fpc_Catalog_ProductController
 */
class Lesti_Fpc_Catalog_ProductController extends Mage_Core_Controller_Front_Action
{

    /**
     *
     */
    public function viewAction()
    {
        $productId = (int)Mage::app()->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->getId()) {
            Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
        }
    }

}