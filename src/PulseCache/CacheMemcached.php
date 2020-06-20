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

/**
 * This class require PECL Memcached extension.
 */
class CacheMemcached extends PulseCache
{
    /**
     * @var Memcached
     */
    protected $memcached;

    /**
     * @var bool Connection status
     */
    protected $is_connected = false;

    public function __construct()
    {
        $this->connect();
        if ($this->isConnected()) {
            $this->memcached->setOption(Memcached::OPT_PREFIX_KEY, _DB_PREFIX_);
            if ($this->memcached->getOption(Memcached::HAVE_IGBINARY)) {
                $this->memcached->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_IGBINARY);
            }
        }
    }

    public function isConnected()
    {
        return $this->is_connected;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Connect to memcached server.
     */
    public function connect()
    {
        if (class_exists('Memcached') && extension_loaded('memcached')) {
            $this->memcached = new Memcached();
        } else {
            return;
        }

        $servers = self::getMemcachedServers();

        if (!$servers) {
            return;
        }
        foreach ($servers as $server) {
            $this->memcached->addServer($server['ip'], $server['port'], (int) $server['weight']);
        }

        $this->is_connected = in_array('255.255.255', $this->memcached->getVersion(), true) === false;
    }

    /**
     * {@inheritdoc}
     */
    protected function _set($key, $value, $ttl = 0)
    {
        if (!$this->isConnected()) {
            return false;
        }

        $result = $this->memcached->set($key, $value, $ttl);

        if ($result === false) {
            if ($this->memcached->getResultCode() === Memcached::RES_E2BIG) {
                $this->setAdjustTableCacheSize(true);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function _get($key)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->memcached->get($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function _exists($key)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->memcached->get($key) !== false;
    }

    /**
     * {@inheritdoc}
     */
    protected function _delete($key)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->memcached->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function _deleteMulti(array $keyArray)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->memcached->deleteMulti($keyArray);
    }

    /**
     * @see Cache::_writeKeys()
     */
    protected function _writeKeys()
    {
        if (!$this->isConnected()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->memcached->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 0)
    {
        return $this->_set($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->_get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return $this->_exists($key);
    }

    /**
     * Delete one or several data from cache (* joker can be used, but avoid it !)
     * 	E.g.: delete('*'); delete('my_prefix_*'); delete('my_key_name');.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete($key)
    {
        if ($key == '*') {
            $this->flush();
        } elseif (strpos($key, '*') === false) {
            $this->_delete($key);
        } else {
            $pattern = str_replace('\\*', '.*', preg_quote($key));
            $keys = $this->memcached->getAllKeys();
            foreach ($keys as $key => $data) {
                if (preg_match('#^' . $pattern . '$#', $key)) {
                    $this->_delete($key);
                }
            }
        }

        return true;
    }

    /**
     * Close connection to memcache server.
     *
     * @return bool
     */
    protected function close()
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->memcached->quit();
    }

    /**
     * Add a memcached server.
     *
     * @param string $ip
     * @param int $port
     * @param int $weight
     *
     * @return bool
     */
    public static function addServer($ip, $port, $weight)
    {
        return PulseDb::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'memcached_servers (ip, port, weight) VALUES(\'' . pSQL($ip) . '\', ' . (int) $port . ', ' . (int) $weight . ')', false);
    }

    /**
     * Get list of memcached servers.
     *
     * @return array
     */
    public static function getMemcachedServers()
    {
        return PulseDb::getInstance(_PULSE_USE_SQL_SLAVE_)->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'memcached_servers', true, false);
    }

    /**
     * Delete a memcached server.
     *
     * @param int $id_server
     *
     * @return bool
     */
    public static function deleteServer($id_server)
    {
        return PulseDb::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'memcached_servers WHERE id_memcached_server=' . (int) $id_server);
    }
}
