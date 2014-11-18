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
 * Class Lesti_Fpc_Test_Model_Observer_Save
 */
class Lesti_Fpc_Test_Model_Observer_Save extends Lesti_Fpc_Test_TestCase
{
    /**
     * @test
     */
    public function testCalaogProductSaveAfter()
    {
        $this->_fpc->save(
            'product1',
            'product1_cache_id',
            array(sha1('product_1'))
        );
        $this->_fpc->save(
            'category1',
            'category1_cache_id',
            array(sha1('category_1'))
        );
        $this->_fpc->save(
            'category2',
            'category2_cache_id',
            array(sha1('category_2'))
        );

        $product = new Mage_Catalog_Model_Product();
        $product->setOrigData(array());
        $product->setCategoryIds(array(1));
        $product->setId(1);
        Mage::dispatchEvent(
            'catalog_product_save_after',
            array('product' => $product)
        );

        $this->assertFalse($this->_fpc->load('product1_cache_id'));
        $this->assertFalse($this->_fpc->load('category1_cache_id'));
        $this->assertEquals(
            'category2',
            $this->_fpc->load('category2_cache_id')
        );
    }

    /**
     * @test
     */
    public function testCatalogCategorySaveAfter()
    {
        $this->_fpc->save(
            'category1',
            'category1_cache_id',
            array(sha1('category_1'))
        );
        $this->_fpc->save(
            'category2',
            'category2_cache_id',
            array(sha1('category_2'))
        );

        $category = new Mage_Catalog_Model_Category();
        $category->setId(1);
        Mage::dispatchEvent(
            'catalog_category_save_after',
            array('category' => $category)
        );

        $this->assertFalse($this->_fpc->load('category1_cache_id'));
        $this->assertEquals(
            'category2',
            $this->_fpc->load('category2_cache_id')
        );
    }

    /**
     * @test
     */
    public function testCmsPageSaveAfter()
    {
        $this->_fpc->save('page1', 'page1_cache_id', array(sha1('cms_1')));
        $this->_fpc->save('page2', 'page2_cache_id', array(sha1('cms_2')));
        $this->_fpc->save('page3', 'page3_cache_id', array(sha1('cms_3')));

        $page = new Mage_Cms_Model_Page();
        $page->setId(1);
        $page->setIdentifier('3');
        Mage::dispatchEvent('cms_page_save_after', array('object' => $page));

        $this->assertFalse($this->_fpc->load('page1_cache_id'));
        $this->assertFalse($this->_fpc->load('page3_cache_id'));
        $this->assertEquals('page2', $this->_fpc->load('page2_cache_id'));
    }

    /**
     * @test
     */
    public function testCmsBlockSaveAfter()
    {
        $this->_fpc->save(
            'page1',
            'page1_cache_id',
            array(sha1('cmsblock_1'))
        );
        $this->_fpc->save(
            'page2',
            'page2_cache_id',
            array(sha1('cmsblock_2'))
        );

        $cmsBlock = new Mage_Cms_Model_Block();
        $cmsBlock->setIdentifier('1');
        Mage::dispatchEvent('model_save_after', array('object' => $cmsBlock));

        $this->assertFalse($this->_fpc->load('page1_cache_id'));
        $this->assertEquals('page2', $this->_fpc->load('page2_cache_id'));
    }

    /**
     * @test
     */
    public function testCatalogProductSaveAfterMassAction()
    {
        $this->_fpc->save(
            'product1',
            'product1_cache_id',
            array(sha1('product_1'))
        );
        $this->_fpc->save(
            'product2',
            'product2_cache_id',
            array(sha1('product_2'))
        );
        $this->_fpc->save(
            'product3',
            'product3_cache_id',
            array(sha1('product_3'))
        );

        $event = new Mage_Index_Model_Event();
        $productAction = new Mage_Catalog_Model_Product_Action();
        $productAction->setProductIds(array(2, 3));
        $event->setType('mass_action');
        $event->setEntity('catalog_product');
        $event->setDataObject($productAction);
        Mage::dispatchEvent('model_save_after', array('object' => $event));

        $this->assertEquals('product1', $this->_fpc->load('product1_cache_id'));
        $this->assertFalse($this->_fpc->load('product2_cache_id'));
        $this->assertFalse($this->_fpc->load('product3_cache_id'));
    }

    /**
     * @test
     */
    public function testCataloginventoryStockItemSaveAfter()
    {
        $this->_fpc->save(
            'product1',
            'product1_cache_id',
            array(sha1('product_1'))
        );
        $this->_fpc->save(
            'product2',
            'product2_cache_id',
            array(sha1('product_2'))
        );

        $item = new Mage_CatalogInventory_Model_Stock_Item();
        $item->setStockStatusChangedAuto(true);
        $item->setProductId(1);
        Mage::dispatchEvent(
            'cataloginventory_stock_item_save_after',
            array('item' => $item)
        );

        $this->assertFalse($this->_fpc->load('product1_cache_id'));
        $this->assertEquals(
            'product2',
            $this->_fpc->load('product2_cache_id')
        );
    }
}
