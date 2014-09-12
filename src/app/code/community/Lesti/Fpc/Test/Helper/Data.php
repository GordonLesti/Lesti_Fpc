<?php
/**
 * Lesti_Fpc
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * http://opensource.org/licenses/OSL-3.0
 *
 * @package      Lesti_Fpc
 * @copyright    Copyright (c) 2013 Gordon Lesti (http://www.gordonlesti.com)
 * @author       Gordon Lesti <info@gordonlesti.com>
 * @license      http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * Test case for Lesti_Fpc_Helper_Data
 * 
 * It extends the controller test case because Lesti_Fpc_Helper_Data::getKeys()
 * needs a session to work.
 */
class Lesti_Fpc_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * Test subject
     * 
     * @var Lesti_Fpc_Helper_Data
     */
    protected $_helper;

    protected function setUp()
    {
        parent::setUp();
        $this->_helper = Mage::helper('fpc');
    }
    /**
     * Test that URI params can be matched by RegEx
     * 
     * @test
     * @loadFixture config.yaml
     * @dataProvider dataProvider
     */
    public function testRegexUriParams($uriParamSets = array(), $expectedCount = 0)
    {
        $actualKeys = array();
        foreach ($uriParamSets as $uriParams) {
            $this->_resetParams();
            Mage::app()->getRequest()->setParams($uriParams);
            $actualKeys[] = $this->_helper->getKey();
        }
        $this->assertCount(
            $expectedCount,
            array_unique($actualKeys),
            sprintf('%d different keys expected', $expectedCount)
        );
    }
    /**
     * Reset parameters in Magento request and FPC
     */
    protected function _resetParams()
    {
        if (Mage::registry(Lesti_Fpc_Helper_Data::REGISTRY_KEY_PARAMS)) {
            Mage::unregister(Lesti_Fpc_Helper_Data::REGISTRY_KEY_PARAMS);
        }
        Mage::app()->getRequest()->clearParams();
    }
}
