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
class Lesti_Fpc_Test_Model_Fpc extends Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Model_Fpc
     */
    protected $_fpc;

    protected function setUp()
    {
        parent::setUp();
        $this->_fpc = Mage::getSingleton('fpc/fpc');
    }

    /**
     * @test
     * @loadFixture save_load_clean.yaml
     */
    public function saveLoadClean()
    {
        $data = 'fpc_data';
        $id = 'fpc_id';
        $tag = 'tag1';

        // check if tag tag1 (clean without array)
        $this->_fpc->save($data, $id, array($tag));
        $this->assertEquals($data, $this->_fpc->load($id));
        $this->_fpc->clean($tag);
        $this->assertFalse($this->_fpc->load($id));

        // check global tag FPC (clean with array)
        $this->_fpc->save($data, $id, array($tag));
        $this->assertEquals($data, $this->_fpc->load($id));
        $this->_fpc->clean(array(Lesti_Fpc_Model_Fpc::CACHE_TAG));
        $this->assertFalse($this->_fpc->load($id));

        // (global clean)
        $this->_fpc->save($data, $id, array($tag));
        $this->assertEquals($data, $this->_fpc->load($id));
        $this->_fpc->clean();
        $this->assertFalse($this->_fpc->load($id));

        // check timeout
        $this->_fpc->save($data, $id, array($tag), 2);
        $this->assertEquals($data, $this->_fpc->load($id));
        sleep(3);
        $this->assertFalse($this->_fpc->load($id));
    }
}