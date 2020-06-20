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
use PulseException\PulseException as PulseException;

class CacheApc extends PulseCache
{
    /** @var bool Whether APCu is enabled */
    public $apcu;

    /**
     * CacheApcCore constructor.
     */
    public function __construct()
    {
        if (!extension_loaded('apc') && !extension_loaded('apcu')) {
            throw new PulseException('APC cache has been enabled, but the APC or APCu extension is not available');
        }
        $this->apcu = extension_loaded('apcu');
    }

    /**
     * Delete one or several data from cache (* joker can be used, but avoid it !)
     * 	E.g.: delete('*'); delete('my_prefix_*'); delete('my_key_name');.
     *
     * @param string $key Cache key
     *
     * @return bool Whether the key was deleted
     */
    public function delete($key)
    {
        if ($key == '*') {
            $this->flush();
        } elseif (strpos($key, '*') === false) {
            $this->_delete($key);
        } else {
            $pattern = str_replace('\\*', '.*', preg_quote($key));

            $cache_info = (($this->apcu) ? apcu_cache_info('') : apc_cache_info(''));
            foreach ($cache_info['cache_list'] as $entry) {
                if (isset($entry['key'])) {
                    $key = $entry['key'];
                } else {
                    $key = $entry['info'];
                }
                if (preg_match('#^' . $pattern . '$#', $key)) {
                    $this->_delete($key);
                }
            }
        }

        return true;
    }

    /**
     * @see Cache::_set()
     */
    protected function _set($key, $value, $ttl = 0)
    {
        $result = (($this->apcu) ? apcu_store($key, $value, $ttl) : apc_store($key, $value, $ttl));
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
        return ($this->apcu) ? apcu_fetch($key) : apc_fetch($key);
    }

    /**
     * @see Cache::_exists()
     */
    protected function _exists($key)
    {
        if (!function_exists('apc_exists') && !function_exists('apcu_exists')) {
            // We're dealing with APC < 3.1.4; use this boolean wrapper as a fallback:
            return (bool) apc_fetch($key);
        } else {
            return ($this->apcu) ? apcu_exists($key) : apc_exists($key);
        }
    }

    /**
     * @see Cache::_delete()
     */
    protected function _delete($key)
    {
        return ($this->apcu) ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * @see Cache::_writeKeys()
     */
    protected function _writeKeys()
    {
    }

    /**
     * @see Cache::flush()
     */
    public function flush()
    {
        return ($this->apcu) ? apcu_clear_cache() : apc_clear_cache();
    }

    /**
     * Store data in the cache.
     *
     * @param string $key Cache Key
     * @param mixed $value Value
     * @param int $ttl Time to live in the cache
     *                 0 = unlimited
     *
     * @return bool Whether the data was successfully stored
     */
    public function set($key, $value, $ttl = 0)
    {
        return $this->_set($key, $value, $ttl);
    }

    /**
     * Retrieve data from the cache.
     *
     * @param string $key Cache key
     *
     * @return mixed Data
     */
    public function get($key)
    {
        return $this->_get($key);
    }

    /**
     * Check if data has been cached.
     *
     * @param string $key Cache key
     *
     * @return bool Whether the data has been cached
     */
    public function exists($key)
    {
        return $this->_exists($key);
    }
}
