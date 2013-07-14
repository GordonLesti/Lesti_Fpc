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
 * Class Lesti_Fpc_Test_Model_Fpc
 */
class Lesti_Fpc_Test_Model_Fpc extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @test
     */
    public function saveAndLoad()
    {
        $fpc = Mage::getSingleton('fpc/fpc');
        $key = 'lesti_fpc';
        $value = 'test';
        $fpc->save($value, $key);
        $this->assertTrue($fpc->load($key) === $value);
        $fpc->remove($key);
        $this->assertTrue($fpc->load($key) === false);
    }

}