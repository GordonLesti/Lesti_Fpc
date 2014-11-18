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
 * Class Lesti_Fpc_Test_Model_Observer
 */
class Lesti_Fpc_Test_Model_Observer extends Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Model_Observer
     */
    protected $_observer;

    protected function setUp()
    {
        parent::setUp();
        $this->_observer = Mage::getSingleton('fpc/observer');
    }

    protected function tearDown()
    {
        parent::tearDown();
        // unregister observer
        Mage::unregister('_singleton/fpc/observer');
        Mage::getSingleton('customer/session')->setData(
            Lesti_Fpc_Helper_Block::LAZY_BLOCKS_VALID_SESSION_PARAM,
            false
        );
    }

    /**
     * @test
     * @loadFixture controller_action_postdispatch.yaml
     * @dataProvider dataProvider
     */
    public function controllerActionPostdispatch(
        $route,
        $controller,
        $action,
        $expected
    )
    {
        Mage::app()->getRequest()->setRouteName($route);
        Mage::app()->getRequest()->setControllerName($controller);
        Mage::app()->getRequest()->setActionName($action);
        Mage::getSingleton('customer/session')->setData(
            Lesti_Fpc_Helper_Block::LAZY_BLOCKS_VALID_SESSION_PARAM,
            true
        );

        $this->_observer->controllerActionPostdispatch();
        $this->assertEquals(
            (bool) $expected,
            Mage::getSingleton('customer/session')->getData(
                Lesti_Fpc_Helper_Block::LAZY_BLOCKS_VALID_SESSION_PARAM
            )
        );
    }
}
