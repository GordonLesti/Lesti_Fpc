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
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Lesti_Fpc_Test_Helper_Block extends Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Helper_Block
     */
    protected $_blockHelper;

    protected function setUp()
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
            array('messages', 'global_messages', 'global_notices'),
            $this->_blockHelper->getDynamicBlocks()
        );
    }

    /**
     * @test
     * @loadFixture get_dynamic_blocks_empty.yaml
     */
    public function testGetDynamicBlocksEmpty()
    {
        $this->assertEquals(array(), $this->_blockHelper->getDynamicBlocks());
    }

    /**
     * @test
     * @loadFixture get_lazy_blocks.yaml
     */
    public function testGetLazyBlocks()
    {
        $this->assertEquals(
            array('top.links', 'cart_sidebar', 'catalog.compare.sidebar'),
            $this->_blockHelper->getLazyBlocks()
        );
    }

    /**
     * @test
     * @loadFixture get_lazy_blocks_empty.yaml
     */
    public function testGetLazyBlocksEmpty()
    {
        $this->assertEquals(array(), $this->_blockHelper->getLazyBlocks());
    }

    /**
     * @test
     * @loadFixture are_lazy_blocks_valid.yaml
     */
    public function testAreLazyBlocksValid()
    {
        // initial should return false
        $this->assertFalse($this->_blockHelper->areLazyBlocksValid());
        // hash should be set and return true
        $this->assertTrue($this->_blockHelper->areLazyBlocksValid());

        // edit host
        $_SERVER['HTTP_HOST'] = 'fpc.dev';
        $this->assertFalse($this->_blockHelper->areLazyBlocksValid());
        $this->assertTrue($this->_blockHelper->areLazyBlocksValid());

        // edit port
        $_SERVER['SERVER_PORT'] = '80';
        $this->assertFalse($this->_blockHelper->areLazyBlocksValid());
        $this->assertTrue($this->_blockHelper->areLazyBlocksValid());

        // edit store code
        $storeCode = Mage::app()->getStore()->getCode();
        Mage::app()->getStore()->setCode('fpc');
        $this->assertFalse($this->_blockHelper->areLazyBlocksValid());
        $this->assertTrue($this->_blockHelper->areLazyBlocksValid());
        Mage::app()->getStore()->setCode($storeCode);

        // edit currency
        Mage::app()->getStore()->setCurrentCurrencyCode('FPC');
        $this->assertFalse($this->_blockHelper->areLazyBlocksValid());
        $this->assertTrue($this->_blockHelper->areLazyBlocksValid());

        // edit customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        if (version_compare(Mage::getVersion(), '1.6.0.0', '<')) {
            $customer = Mage::getModel('customer/customer')->load(1);
            $customerSession->setCustomerAsLoggedIn($customer);
        } else {
            $customerSession->setCustomerGroupId(78);
        }
        $this->assertFalse($this->_blockHelper->areLazyBlocksValid());
        $this->assertTrue($this->_blockHelper->areLazyBlocksValid());

        // edit design package name
        Mage::getDesign()->setPackageName('base');
        $this->assertFalse($this->_blockHelper->areLazyBlocksValid());
        $this->assertTrue($this->_blockHelper->areLazyBlocksValid());

        // edit design theme
        Mage::getDesign()->setTheme('template', 'FPC');
        $this->assertFalse($this->_blockHelper->areLazyBlocksValid());
        $this->assertTrue($this->_blockHelper->areLazyBlocksValid());
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
