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
 * Class Lesti_Fpc_Test_Model_Observer_Tags
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Lesti_Fpc_Test_Model_Observer_Tags extends Lesti_Fpc_Test_TestCase
{
    /**
     * @test
     * @loadFixture fpc_observer_collect_cache_tags.yaml
     * @dataProvider dataProvider
     */
    public function testFpcObserverCollectCacheTags(
        $routeName,
        $controllerName,
        $actionName,
        $expectedCacheTags,
        $params = array()
    )
    {
        $this->setFullActionName($routeName, $controllerName, $actionName);
        Mage::app()->getRequest()->setParams($params);

        $tags = array('fpc_tag');
        $cacheTags = $this->dispatchCollectTagsEvent($tags);
        $expectedCacheTags = array_merge($tags, $expectedCacheTags);
        $this->assertEquals($expectedCacheTags, $cacheTags);
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
        $this->setFullActionName('cms', 'index', 'index');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
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
        $this->setFullActionName('cms', 'index', 'index');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     */
    public function testGetCmsPageViewCacheTagsEmpty()
    {
        $expectedCacheTags = array(
            '293ae992f45cff1d17d3e83eefd2285d47f7c997', // sha1('cms')
        );
        $this->setFullActionName('cms', 'page', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     */
    public function testGetCmsPageViewCacheTagsPageId()
    {
        $expectedCacheTags = array(
            '293ae992f45cff1d17d3e83eefd2285d47f7c997', // sha1('cms')
            '133a307e26568a3c9c93e60b0e314945b91d446f', // sha1('cms_5')
        );
        $request = Mage::app()->getRequest();
        $request->setParam('page_id', 5);
        $this->setFullActionName('cms', 'page', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     */
    public function testGetCmsPageViewCacheTagsId()
    {
        $expectedCacheTags = array(
            '293ae992f45cff1d17d3e83eefd2285d47f7c997', // sha1('cms')
            '133a307e26568a3c9c93e60b0e314945b91d446f', // sha1('cms_5')
        );
        $request = Mage::app()->getRequest();
        $request->setParam('id', 5);
        $this->setFullActionName('cms', 'page', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     */
    public function testGetCatalogProductViewCacheTagsZero()
    {
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
        );
        $this->setFullActionName('catalog', 'product', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     */
    public function testGetCatalogProductViewCacheTagsSimpleProduct()
    {
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
        );
        $request = Mage::app()->getRequest();
        $request->setParam('id', 5);
        $this->setFullActionName('catalog', 'product', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     */
    public function testGetCatalogProductViewCacheTagsWithCategory()
    {
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
            '5ccbf9c9c5fc1bc34df8238a97094968f38f5165', // sha1('category')
            '48ce6d1a1ef87339c758621f81e33b02f9d1cb72', // sha1('category_7')
        );
        $request = Mage::app()->getRequest();
        $request->setParam('id', 5);
        $request->setParam('category', 7);
        $this->setFullActionName('catalog', 'product', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     * @loadFixture get_catalog_product_view_cache_tags_configurable_prod.yaml
     */
    public function testGetCatalogProductViewCacheTagsConfigurableProduct()
    {
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
            'cfe471971355a3a3d4311e12813f7fa689cf5199', // sha1('product_6')
        );
        $request = Mage::app()->getRequest();
        $request->setParam('id', 5);
        $this->setFullActionName('catalog', 'product', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);

        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            'cfe471971355a3a3d4311e12813f7fa689cf5199', // sha1('product_6')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
        );
        $request->setParam('id', 6);
        $this->setFullActionName('catalog', 'product', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     * @loadFixture get_catalog_product_view_cache_tags_grouped_product.yaml
     */
    public function testGetCatalogProductViewCacheTagsGroupedProduct()
    {
        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
            'cfe471971355a3a3d4311e12813f7fa689cf5199', // sha1('product_6')
        );
        $request = Mage::app()->getRequest();
        $request->setParam('id', 5);
        $this->setFullActionName('catalog', 'product', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);

        $expectedCacheTags = array(
            '38a007151abe87cc01a5b6e9cc418e85286e2087', // sha1('product')
            'cfe471971355a3a3d4311e12813f7fa689cf5199', // sha1('product_6')
            '01337f5c00647634e8cef67064d9c4fd4fa0290e', // sha1('product_5')
        );
        $request->setParam('id', 6);
        $this->setFullActionName('catalog', 'product', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     */
    public function testGetCatalogCategoryViewCacheTagsEmpty()
    {
        $expectedCacheTags = array(
            '5ccbf9c9c5fc1bc34df8238a97094968f38f5165', // sha1('category')
        );
        $this->setFullActionName('catalog', 'category', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @test
     */
    public function testGetCatalogCategoryViewCacheTags()
    {
        $expectedCacheTags = array(
            '5ccbf9c9c5fc1bc34df8238a97094968f38f5165', // sha1('category')
            '48ce6d1a1ef87339c758621f81e33b02f9d1cb72', // sha1('category_7')
        );
        $request = Mage::app()->getRequest();
        $request->setParam('id', 7);
        $this->setFullActionName('catalog', 'category', 'view');
        $cacheTags = $this->dispatchCollectTagsEvent(array());
        $this->assertEquals($expectedCacheTags, $cacheTags);
    }

    /**
     * @param array $tags
     * @return mixed
     */
    protected function dispatchCollectTagsEvent(array $tags)
    {
        $cacheTags = new Varien_Object();
        $cacheTags->setValue($tags);
        Mage::dispatchEvent(
            'fpc_observer_collect_cache_tags',
            array('cache_tags' => $cacheTags)
        );
        return $cacheTags->getValue();
    }
}
