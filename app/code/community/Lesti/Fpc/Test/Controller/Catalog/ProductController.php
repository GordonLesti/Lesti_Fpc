<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 10/19/14
 * Time: 1:56 PM
 * To change this template use File | Settings | File Templates.
 */

/**
 * Class Lesti_Fpc_Test_Controller_Catalog_ProductController
 */
class Lesti_Fpc_Test_Controller_Catalog_ProductController extends
    Lesti_Fpc_Test_TestCase
{
    /**
     * @test
     */
    public function testViewActionWithoutProduct()
    {
        $this->dispatch('fpc/catalog_product/view');
        $this->assertEventNotDispatched('catalog_controller_product_view');
    }

    /**
     * @test
     * @loadFixture view_action_with_product.yaml
     */
    public function testViewActionWithProduct()
    {
        Mage::app()->getRequest()->setParam('id', 5);
        $this->dispatch('fpc/catalog_product/view');
        $this->assertEventDispatched('catalog_controller_product_view');
    }
}
