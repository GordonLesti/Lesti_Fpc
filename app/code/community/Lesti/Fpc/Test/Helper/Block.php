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
 * Class Lesti_Fpc_Test_Helper_Block
 */
class Lesti_Fpc_Test_Helper_Block extends Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Helper_Block
     */
    protected $_blockHelper;

    public function setUp()
    {
        parent::setUp();
        $this->_blockHelper = Mage::helper('fpc/block');
    }

    /**
     * @test
     * @loadFixture get_dynamic_blocks.yaml
     */
    public function testGetDynamicBlocks()
    {
        $this->assertEquals(
            array('foo', 'bar', 'foobar'),
            $this->_blockHelper->getDynamicBlocks()
        );
    }

    /**
     * @test
     * @loadFixture get_lazy_blocks.yaml
     */
    public function testGetLazyBlocks()
    {
        $this->assertEquals(
            array('foo', 'bar', 'foobar'),
            $this->_blockHelper->getLazyBlocks()
        );
    }

    /**
     * @test
     */
    public function testAreLazyBlocksValid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     */
    public function testGetPlaceholderHtml()
    {
        $this->assertEquals(
            '<!-- fpc 0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33 -->',
            $this->_blockHelper->getPlaceholderHtml('foo')
        );
    }

    /**
     * @test
     */
    public function testGetKey()
    {
        $this->assertEquals(
            '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33_block',
            $this->_blockHelper->getKey('foo')
        );
    }

    /**
     * @test
     * @loadFixture use_recently_viewed_products_true.yaml
     */
    public function testUseRecentlyViewedProductsTrue()
    {
        $this->assertTrue($this->_blockHelper->useRecentlyViewedProducts());
    }

    /**
     * @test
     * @loadFixture use_recently_viewed_products_false.yaml
     */
    public function testUseRecentlyViewedProductsFalse()
    {
        $this->assertFalse($this->_blockHelper->useRecentlyViewedProducts());
    }

    /**
     * @test
     */
    public function testGetCacheTagsEmpty()
    {
        $block = new Mage_Core_Block_Template();
        $block->setNameInLayout('foo');
        $this->assertEquals(array(), $this->_blockHelper->getCacheTags($block));
    }

    /**
     * @test
     */
    public function testGetCacheTagsProductList()
    {
        $productOne = new Mage_Catalog_Model_Product();
        $productOne->setId(1);
        $productTwo = new Mage_Catalog_Model_Product();
        $productTwo->setId(2);
        $block = new Mage_Core_Block_Template();
        $block->setNameInLayout('product_list');
        $block->setLoadedProductCollection(array($productOne, $productTwo));
        $this->assertEquals(
            array(
                '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
                '65dd4967fe508e9ebad619a8c976beabf46588fe', // sha1('product_1')
                '499ed21cb19c984d31e23b94a60730520afa8181' // sha1('product_2')
            ),
            $this->_blockHelper->getCacheTags($block)
        );
    }

    /**
     * @test
     */
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
