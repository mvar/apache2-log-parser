<?php
/**
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

class KeysHolder
{
    /**
     * @var array
     */
    protected $storage = array();

    /**
     * Stores key to local storage and returns it's index
     *
     * @param string $namespace
     * @param string $key
     *
     * @return int Stored key index
     */
    public function add($namespace, $key)
    {
        $this->storage[$namespace][] = $key;

        return count($this->storage[$namespace]) - 1;
    }

    /**
     * Returns key from local storage
     *
     * @param string $namespace
     * @param int    $index
     *
     * @return string
     */
    public function get($namespace, $index)
    {
        // TODO: check if exists

        return $this->storage[$namespace][$index];
    }

    /**
     * Returns names of all stored namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        return array_keys($this->storage);
    }
}
