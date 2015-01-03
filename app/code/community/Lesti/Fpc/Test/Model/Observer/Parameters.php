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
 * Class Lesti_Fpc_Test_Model_Observer_Parameters
 */
class Lesti_Fpc_Test_Model_Observer_Parameters extends Lesti_Fpc_Test_TestCase
{
    /**
     * @test
     * @loadFixture fpc_helper_collect_params.yaml
     */
    public function testFpcHelperCollectParams()
    {
        Mage::app()->getRequest()->setRouteName('catalog');
        Mage::app()->getRequest()->setControllerName('category');
        Mage::app()->getRequest()->setActionName('view');

        /** @var Mage_Catalog_Model_Session $catalogSession */
        $catalogSession = Mage::getSingleton('catalog/session');
        $catalogSession->setData('fpc', 'cool');

        $expectedResult = array(
            'fpc' => 'cool',
            'store' => Mage::app()->getStore(true)->getCode(),
            'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
            'design' => Mage::getDesign()->getPackageName().'_'.
                Mage::getDesign()->getTheme('template'),
            'session_fpc' => 'cool',
        );
        $params = array('fpc' => 'cool');
        $object = new Varien_Object();
        $object->setValue($params);
        Mage::dispatchEvent(
            'fpc_helper_collect_params',
            array('parameters' => $object)
        );
        $this->assertEquals($expectedResult, $object->getValue());
    }
}
