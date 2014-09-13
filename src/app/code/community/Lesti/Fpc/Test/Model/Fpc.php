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