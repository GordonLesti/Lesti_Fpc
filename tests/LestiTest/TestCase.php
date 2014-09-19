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
 * Class LestiTest_TestCase
 */
abstract class LestiTest_TestCase extends PHPUnit_Framework_TestCase
{
    protected $_cacheOptions;

    protected $_cache;

    public function setUp()
    {
        Mage::unregister('_singleton/fpc/fpc');
        $this->_cache = Mage::app()->getCacheInstance();
        $this->_cache->flush();
        $this->_cacheOptions = Mage::getResourceSingleton('core/cache')->getAllOptions();
        $cacheOptions = $this->_cacheOptions;
        foreach (array_keys($cacheOptions) as $cache) {
            $cacheOptions[$cache] = $cache == 'fpc' ? 1 : 0;
        }
        if (!array_key_exists('fpc', $cacheOptions)) {
            $cacheOptions['fpc'] = 1;
        }
        $this->_cache->saveOptions($cacheOptions);
        $reflector = new ReflectionClass('Mage_Core_Model_Cache');
        $initOptionsMethod = $reflector->getMethod('_initOptions');
        $initOptionsMethod->setAccessible(true);
        $initOptionsMethod->invokeArgs($this->_cache, array());
    }

    public function tearDown()
    {
        $this->_cache->saveOptions($this->_cacheOptions);
    }
}
