<?php
/**
*    2015 S O F T I N T H E B O X
*
* NOTICE OF LICENSE
*
* It is also available through the world-wide-web at this URL:
* http://www.pulseframework.com/developer-platform
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to hello@pedroteixeira.pro so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this to newer
* versions in the future. If you wish to customize this for your
* needs please refer to http://www.pulseframework.com for more information.
*
*  @author Pedro Teixeira - Pulse Framework <me@pedroteixeira.pro>
*  @copyright  2015 Pulse Framework
*  @license    http://www.pulseframework.com/license
*  International Registered Trademark & Property of Pulse Framework
*/

namespace PulseCache;

use PulseCache\PulseCache;

class CacheXcache extends PulseCache
{
    public function __construct()
    {
        $this->keys = xcache_get(self::KEYS_NAME);
        if (!is_array($this->keys)) {
            $this->keys = array();
        }
    }

    /**
     * @see Cache::_set()
     */
    protected function _set($key, $value, $ttl = 0)
    {
        $result = xcache_set($key, $value, $ttl);

        if ($result === false) {
            $this->setAdjustTableCacheSize(true);
        }

        return $result;
    }

    /**
     * @see Cache::_get()
     */
    protected function _get($key)
    {
        return xcache_isset($key) ? xcache_get($key) : false;
    }

    /**
     * @see Cache::_exists()
     */
    protected function _exists($key)
    {
        return xcache_isset($key);
    }

    /**
     * @see Cache::_delete()
     */
    protected function _delete($key)
    {
        return xcache_unset($key);
    }

    /**
     * @see Cache::_writeKeys()
     */
    protected function _writeKeys()
    {
        xcache_set(self::KEYS_NAME, $this->keys);
    }

    /**
     * @see Cache::flush()
     */
    public function flush()
    {
        $this->delete('*');

        return true;
    }
}
