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
 * Class Lesti_Fpc_Catalog_ProductController
 */
class Lesti_Fpc_Catalog_ProductController extends
    Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $productId = (int)Mage::app()->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->getId()) {
            Mage::dispatchEvent(
                'catalog_controller_product_view',
                array('product' => $product)
            );
        }
    }
}
