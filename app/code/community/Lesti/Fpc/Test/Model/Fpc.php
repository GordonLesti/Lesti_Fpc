<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 17.06.13
 * Time: 20:11
 * To change this template use File | Settings | File Templates.
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