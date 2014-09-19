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
 * Class LestiTest_Fpc_Block_Catalog_Product_View_AjaxTest
 */
class LestiTest_Fpc_Block_Catalog_Product_View_AjaxTest extends LestiTest_TestCase
{
    /**
     * @var Lesti_Fpc_Block_Catalog_Product_View_Ajax
     */
    protected $_catalogProductViewAjaxBlock;

    public function setUp()
    {
        parent::setUp();
        $this->_catalogProductViewAjaxBlock = new Lesti_Fpc_Block_Catalog_Product_View_Ajax();
    }

    public function testGetAjaxUrlFpcInactive()
    {
        Mage::getSingleton('fpc/fpc')->banUse('fpc');
        $this->assertFalse(Mage::getSingleton('fpc/fpc')->isActive());
        $this->assertFalse($this->_catalogProductViewAjaxBlock->getAjaxUrl());
    }

    public function testGetAjaxUrlMissCacheAbleAction()
    {
        $this->assertTrue(Mage::getSingleton('fpc/fpc')->isActive());
        $cacheAbleActionsConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS);
        Mage::app()->getStore()->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS, '');
        $this->assertFalse($this->_catalogProductViewAjaxBlock->getAjaxUrl());
        Mage::app()->getStore()
            ->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS, $cacheAbleActionsConfig);
    }

    public function testGetAjaxUrlDisabledRecentlyViewedProducts()
    {
        $this->assertTrue(Mage::getSingleton('fpc/fpc')->isActive());
        $useRecentlyViewedProductsConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH);
        Mage::app()->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH, false);
        $this->assertFalse($this->_catalogProductViewAjaxBlock->getAjaxUrl());
        Mage::app()->getStore()
            ->setConfig(
                Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH,
                $useRecentlyViewedProductsConfig
            );
    }

    public function testGetAjaxUrl()
    {
        // activate fpc
        $this->assertTrue(Mage::getSingleton('fpc/fpc')->isActive());
        // catalog_product_view as cacheable action
        $cacheAbleActionsConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS);
        Mage::app()->getStore()->setConfig(
            Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS,
            'catalog_product_view'
        );
        // enable recentlyViewedProducts
        $useRecentlyViewedProductsConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH);
        Mage::app()->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH, true);
        // set current product
        $product = new Mage_Catalog_Model_Product();
        $product->setId(5);
        $currentProduct = Mage::registry('current_product');
        Mage::unregister('current_product');
        Mage::register('current_product', $product);
        // set baseUrl
        $baseUrl = Mage::app()->getStore()->setConfig('web/unsecure/base_link_url');
        Mage::app()->getStore()->setConfig('web/unsecure/base_link_url', 'http://localhost/');

        $this->assertEquals(
            'http://localhost/fpc/catalog_product/view/id/5/',
            $this->_catalogProductViewAjaxBlock->getAjaxUrl()
        );
        // restore configs and unregister
        Mage::app()->getStore()
            ->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS, $cacheAbleActionsConfig);
        Mage::app()->getStore()
            ->setConfig(
                Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH,
                $useRecentlyViewedProductsConfig
            );
        Mage::unregister('current_product');
        Mage::register('current_product', $currentProduct);
        Mage::app()->getStore()->setConfig('web/unsecure/base_link_url', $baseUrl);
    }

    public function test_GetProductId()
    {
        $reflector = new ReflectionClass('Lesti_Fpc_Block_Catalog_Product_View_Ajax');
        $getProductIdMethod = $reflector->getMethod('_getProductId');
        $getProductIdMethod->setAccessible(true);
        // set current product
        $product = new Mage_Catalog_Model_Product();
        $product->setId(5);
        $currentProduct = Mage::registry('current_product');
        Mage::unregister('current_product');
        Mage::register('current_product', $product);

        $result = $getProductIdMethod->invokeArgs($this->_catalogProductViewAjaxBlock, array());
        $this->assertEquals(5, $result);
        // unregister
        Mage::unregister('current_product');
        Mage::register('current_product', $currentProduct);
    }

    public function test_GetProductIdFalse()
    {
        $reflector = new ReflectionClass('Lesti_Fpc_Block_Catalog_Product_View_Ajax');
        $getProductIdMethod = $reflector->getMethod('_getProductId');
        $getProductIdMethod->setAccessible(true);
        // set current product
        $currentProduct = Mage::registry('current_product');
        Mage::unregister('current_product');

        $result = $getProductIdMethod->invokeArgs($this->_catalogProductViewAjaxBlock, array());
        $this->assertFalse($result);
        // unregister
        Mage::unregister('current_product');
        Mage::register('current_product', $currentProduct);
    }
}
