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
 * Class Lesti_Fpc_Test_Helper_Data_Tag
 */
class Lesti_Fpc_Test_Helper_Data_Tag extends Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Helper_Data_Tag
     */
    protected $_tagHelper;

    public function setUp()
    {
        parent::setUp();
        $this->_tagHelper = Mage::helper('fpc/data_tag');
    }

    /**
     * @test
     * @loadFixture get_cms_index_index_cache_tags_zero.yaml
     */
    public function testGetCmsIndexIndexCacheTagsZero()
    {
        $expectedCacheTags = array(
            '293ae992f45cff1d17d3e83eefd2285d47f7c997', // sha1('cms')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCmsIndexIndexCacheTags()
        );
    }

    /**
     * @test
     * @loadFixture get_cms_index_index_cache_tags.yaml
     */
    public function testGetCmsIndexIndexCacheTags()
    {
        $expectedCacheTags = array(
            '293ae992f45cff1d17d3e83eefd2285d47f7c997', // sha1('cms')
            '133a307e26568a3c9c93e60b0e314945b91d446f', // sha1('cms_5')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCmsIndexIndexCacheTags()
        );
    }

    /**
     * @test
     */
    public function testGetCmsPageViewCacheTagsEmpty()
    {
        $request = Mage::app()->getRequest();
        $expectedCacheTags = array(
            '293ae992f45cff1d17d3e83eefd2285d47f7c997', // sha1('cms')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCmsPageViewCacheTags($request)
        );
    }

    /**
     * @test
     */
    public function testGetCmsPageViewCacheTagsPageId()
    {
        $request = Mage::app()->getRequest();
        $request->setParam('page_id', 5);
        $expectedCacheTags = array(
            '293ae992f45cff1d17d3e83eefd2285d47f7c997', // sha1('cms')
            '133a307e26568a3c9c93e60b0e314945b91d446f', // sha1('cms_5')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCmsPageViewCacheTags($request)
        );
    }

    /**
     * @test
     */
    public function testGetCmsPageViewCacheTagsId()
    {
        $request = Mage::app()->getRequest();
        $request->setParam('id', 5);
        $expectedCacheTags = array(
            '293ae992f45cff1d17d3e83eefd2285d47f7c997', // sha1('cms')
            '133a307e26568a3c9c93e60b0e314945b91d446f', // sha1('cms_5')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCmsPageViewCacheTags($request)
        );
    }

    /**
     * @test
     */
    public function testGetCatalogProductViewCacheTagsZero()
    {
        $request = Mage::app()->getRequest();
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCatalogProductViewCacheTags($request)
        );
    }

    /**
     * @test
     */
    public function testGetCatalogProductViewCacheTagsSimpleProduct()
    {
        $request = Mage::app()->getRequest();
        $request->setParam('id', 5);
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCatalogProductViewCacheTags($request)
        );
    }

    /**
     * @test
     */
    public function testGetCatalogProductViewCacheTagsWithCategory()
    {
        $request = Mage::app()->getRequest();
        $request->setParam('id', 5);
        $request->setParam('category', 7);
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
            '5ccbf9c9c5fc1bc34df8238a97094968f38f5165', // sha1('category')
            '48ce6d1a1ef87339c758621f81e33b02f9d1cb72', // sha1('category_7')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCatalogProductViewCacheTags($request)
        );
    }

    /**
     * @test
     * @loadFixture get_catalog_product_view_cache_tags_configurable_product.yaml
     */
    public function testGetCatalogProductViewCacheTagsConfigurableProduct()
    {
        $request = Mage::app()->getRequest();

        $request->setParam('id', 5);
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
            'cfe471971355a3a3d4311e12813f7fa689cf5199', // sha1('product_6')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCatalogProductViewCacheTags($request)
        );

        $request->setParam('id', 6);
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            'cfe471971355a3a3d4311e12813f7fa689cf5199', // sha1('product_6')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
        );
        $this->assertEquals(
            $expectedCacheTags,
            $this->_tagHelper->getCatalogProductViewCacheTags($request)
        );
    }
}
