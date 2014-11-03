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
 * Class Lesti_Fpc_Test_Block_Catalog_Product_View_Ajax
 */
class Lesti_Fpc_Test_Block_Catalog_Product_View_Ajax extends
    Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Block_Catalog_Product_View_Ajax
     */
    protected $_catalogProductViewAjaxBlock;

    protected function setUp()
    {
        parent::setUp();
        $this->_catalogProductViewAjaxBlock =
            new Lesti_Fpc_Block_Catalog_Product_View_Ajax();
        // register a product
        $product = new Mage_Catalog_Model_Product();
        $product->setId(5);
        Mage::register('current_product', $product);
    }

    protected function tearDown()
    {
        parent::tearDown();
        Mage::unregister('current_product');
    }

    /**
     * @test
     * @loadFixture get_ajax_url_fpc_inactive.yaml
     */
    public function testGetAjaxUrlFpcInactive()
    {
        Mage::app()->getCacheInstance()->banUse('fpc');
        $this->assertFalse($this->_catalogProductViewAjaxBlock->getAjaxUrl());
    }

    /**
     * @test
     * @loadFixture get_ajax_url_miss_cache_able_action.yaml
     */
    public function testGetAjaxUrlMissCacheAbleAction()
    {
        $this->assertFalse($this->_catalogProductViewAjaxBlock->getAjaxUrl());
    }

    /**
     * @test
     * @loadFixture get_ajax_url_disabled_recently_viewed_products.yaml
     */
    public function testGetAjaxUrlDisabledRecentlyViewedProducts()
    {
        $this->assertFalse($this->_catalogProductViewAjaxBlock->getAjaxUrl());
    }

    /**
     * @test
     * @loadFixture get_ajax_url_no_current_product.yaml
     */
    public function testGetAjaxUrlNoCurrentProduct()
    {
        Mage::unregister('current_product');
        $this->assertFalse($this->_catalogProductViewAjaxBlock->getAjaxUrl());
    }

    /**
     * @test
     * @loadFixture get_ajax_url.yaml
     */
    public function testGetAjaxUrl()
    {
        // clean baseUrlCache of Model/Store
        $this->clearBaseUrlProperty();
        $session = Mage::getSingleton('core/session');
        $sid = $session->getEncryptedSessionId();
        $expectedUrl = 'http://magento.dev/fpc/catalog_product/view/id/5/';
        if ($sid) {
            $expectedUrl .= '?'.$session->getSessionIdQueryParam().'='.$sid;
        }

        $this->assertEquals(
            $expectedUrl,
            $this->_catalogProductViewAjaxBlock->getAjaxUrl()
        );
    }
}
