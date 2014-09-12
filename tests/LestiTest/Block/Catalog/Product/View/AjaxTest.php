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
 * Class LestiTest_Fpc_Block_Catalog_Product_View_Ajax
 */
class LestiTest_Fpc_Block_Catalog_Product_View_Ajax extends PHPUnit_Framework_TestCase
{
    protected $_cacheOptions;

    protected $_cache;

    public function setUp()
    {
        Mage::init();
        $this->_cache = Mage::app()->getCacheInstance();
        $this->_cache->flush();
        $this->_cacheOptions = Mage::getResourceSingleton('core/cache')->getAllOptions();
        $cacheOptions = $this->_cacheOptions;
        foreach ($cacheOptions as $cache => $value) {
            $cacheOptions[$cache] = $cache == 'fpc' ? 1 : 0;
        }
        $this->_cache->saveOptions($cacheOptions);
    }

    public function tearDown()
    {
        $this->_cache->saveOptions($this->_cacheOptions);
    }

    public function testGetAjaxUrlFpcInactive()
    {
        Mage::app()->getCacheInstance()->banUse('fpc');
        $this->assertFalse(Mage::getSingleton('fpc/fpc')->isActive());
        $catalogProductViewAjaxBlock = new Lesti_Fpc_Block_Catalog_Product_View_Ajax();
        $this->assertFalse($catalogProductViewAjaxBlock->getAjaxUrl());
    }

    public function testGetAjaxUrlMissCacheAbleAction()
    {
        $this->assertTrue(Mage::getSingleton('fpc/fpc')->isActive());
        $cacheAbleActionsConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS);
        Mage::app()->getStore()->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS, '');
        $catalogProductViewAjaxBlock = new Lesti_Fpc_Block_Catalog_Product_View_Ajax();
        $this->assertFalse($catalogProductViewAjaxBlock->getAjaxUrl());
        Mage::app()->getStore()
            ->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS, $cacheAbleActionsConfig);
    }

    public function testGetAjaxUrlDisabledRecentlyViewedProducts()
    {
        $this->assertTrue(Mage::getSingleton('fpc/fpc')->isActive());
        $useRecentlyViewedProductsConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH);
        Mage::app()->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH, false);
        $catalogProductViewAjaxBlock = new Lesti_Fpc_Block_Catalog_Product_View_Ajax();
        $this->assertFalse($catalogProductViewAjaxBlock->getAjaxUrl());
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
        Mage::register('current_product', $product);
        // set baseUrl
        $baseUrl = Mage::app()->getStore()->setConfig('web/unsecure/base_url');
        Mage::app()->getStore()->setConfig('web/unsecure/base_url', 'http://localhost/');

        $catalogProductViewAjaxBlock = new Lesti_Fpc_Block_Catalog_Product_View_Ajax();
        $this->assertEquals(
            'http://localhost/fpc/catalog_product/view/id/5/',
            $catalogProductViewAjaxBlock->getAjaxUrl()
        );
        // restore configs
        Mage::app()->getStore()
            ->setConfig(Lesti_Fpc_Helper_Data::XML_PATH_CACHEABLE_ACTIONS, $cacheAbleActionsConfig);
        Mage::app()->getStore()
            ->setConfig(
                Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH,
                $useRecentlyViewedProductsConfig
            );
        Mage::unregister('current_product');
        Mage::app()->getStore()->setConfig('web/unsecure/base_url', $baseUrl);
    }
}
