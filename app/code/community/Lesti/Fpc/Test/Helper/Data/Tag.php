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
}
