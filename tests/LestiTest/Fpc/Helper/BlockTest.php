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
 * Class LestiTest_Fpc_Helper_BlockTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class LestiTest_Fpc_Helper_BlockTest extends LestiTest_TestCase
{
    /**
     * @var Lesti_Fpc_Helper_Block
     */
    protected $_blockHelper;

    public function setUp()
    {
        parent::setUp();
        $this->_blockHelper = new Lesti_Fpc_Helper_Block();
    }

    public function testGetDynamicBlocks()
    {
        $dynamicBlocksConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Block::DYNAMIC_BLOCKS_XML_PATH);
        $dynamicBlocks = " foo,\nbar, foobar \n , bar";
        Mage::app()->getStore()->setConfig(
            Lesti_Fpc_Helper_Block::DYNAMIC_BLOCKS_XML_PATH,
            $dynamicBlocks
        );
        $expectedResult = array('foo', 'bar', 'foobar');
        $this->assertEquals($expectedResult, $this->_blockHelper->getDynamicBlocks());
        // restore configs
        Mage::app()->getStore()->setConfig(
            Lesti_Fpc_Helper_Block::DYNAMIC_BLOCKS_XML_PATH,
            $dynamicBlocksConfig
        );
    }

    public function testGetLazyBlocks()
    {
        $lazyBlocksConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Block::LAZY_BLOCKS_XML_PATH);
        $lazyBlocks = " foo,\nbar, foobar \n , bar";
        Mage::app()->getStore()->setConfig(
            Lesti_Fpc_Helper_Block::LAZY_BLOCKS_XML_PATH,
            $lazyBlocks
        );
        $expectedResult = array('foo', 'bar', 'foobar');
        $this->assertEquals($expectedResult, $this->_blockHelper->getLazyBlocks());
        // restore configs
        Mage::app()->getStore()->setConfig(
            Lesti_Fpc_Helper_Block::LAZY_BLOCKS_XML_PATH,
            $lazyBlocksConfig
        );
    }

    public function testAreLazyBlocksValid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function test_getLazyBlocksValidHash()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testGetPlaceholderHtml()
    {
        $this->assertEquals(
            '<!-- fpc 0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33 -->',
            $this->_blockHelper->getPlaceholderHtml('foo')
        );
    }

    public function testGetKey()
    {
        $this->assertEquals(
            '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33_block',
            $this->_blockHelper->getKey('foo')
        );
    }

    public function testUseRecentlyViewedProductsTrue()
    {
        // enable recentlyViewedProducts
        $useRecentlyViewedProductsConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH);
        Mage::app()->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH, true);
        $this->assertTrue($this->_blockHelper->useRecentlyViewedProducts());
        // restore configs
        Mage::app()->getStore()
            ->setConfig(
                Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH,
                $useRecentlyViewedProductsConfig
            );
    }

    public function testUseRecentlyViewedProductsFalse()
    {
        // enable recentlyViewedProducts
        $useRecentlyViewedProductsConfig = Mage::app()
            ->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH);
        Mage::app()->getStore()->setConfig(Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH, false);
        $this->assertFalse($this->_blockHelper->useRecentlyViewedProducts());
        // restore configs
        Mage::app()->getStore()
            ->setConfig(
                Lesti_Fpc_Helper_Block::USE_RECENTLY_VIEWED_PRODUCTS_XML_PATH,
                $useRecentlyViewedProductsConfig
            );
    }

    public function testGetCacheTagsEmpty()
    {
        $block = new Mage_Core_Block_Template();
        $block->setNameInLayout('foo');
        $this->assertEquals(array(), $this->_blockHelper->getCacheTags($block));
    }

    public function testGetCacheTagsProductList()
    {
        $product1 = new Mage_Catalog_Model_Product();
        $product1->setId(1);
        $product2 = new Mage_Catalog_Model_Product();
        $product2->setId(2);
        $block = new Mage_Core_Block_Template();
        $block->setNameInLayout('product_list');
        $block->setLoadedProductCollection(array($product1, $product2));
        $this->assertEquals(
            array(
                '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
                '65dd4967fe508e9ebad619a8c976beabf46588fe', // sha1('product_1')
                '499ed21cb19c984d31e23b94a60730520afa8181'  // sha1('product_2')
            ),
            $this->_blockHelper->getCacheTags($block)
        );
    }

    public function testGetCacheTagsCmsBlock()
    {
        $block = new Mage_Cms_Block_Block();
        $block->setNameInLayout('foo');
        $block->setId(5);
        $this->assertEquals(
            array(
                '74709cfbbdffe24885db05ff5d08ea9c13663422',
                'ba750b74090c01fda30a383d301af7e15b340928'
            ),
            $this->_blockHelper->getCacheTags($block)
        );
    }
}
