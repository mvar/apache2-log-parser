<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

class KeyBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for add() and get() methods
     *
     * @param string $namespace
     * @param string $key
     *
     * @dataProvider getTestAddAndGetData()
     */
    public function testAddAndGet($namespace, $key)
    {
        $holder = new KeyBag();
        $index = $holder->add($namespace, $key);

        $this->assertEquals($key, $holder->get($namespace, $index));
    }

    /**
     * Test for get() when nothing is set
     */
    public function testGetNull()
    {
        $holder = new KeyBag();

        $this->assertNull($holder->get('ns', 'test'));
    }

    /**
     * Test for getNamespaces()
     */
    public function testGetNamespaces()
    {
        $data = array(
            array('namespace_1', 'key_11'),
            array('namespace_2', 'key_21'),
        );

        $holder = new KeyBag();
        $namespaces = array();

        foreach ($data as $row) {
            list($namespace, $key) = $row;
            $namespaces[] = $namespace;
            $holder->add($namespace, $key);
        }

        $this->assertEquals($namespaces, $holder->getNamespaces());
    }

    /**
     * Test for registerNamespace()
     */
    public function testRegisterNamespace()
    {
        $namespaces = array('ns_1');

        $holder = new KeyBag();
        $holder->registerNamespace($namespaces[0]);

        $this->assertEquals($namespaces, $holder->getNamespaces());
    }

    /**
     * Data provider for testAddAndGet()
     *
     * @return array[]
     */
    public function getTestAddAndGetData()
    {
        return array(
            array('namespace_1', 'key_11'),
            array('namespace_2', 'key_21'),
        );
    }
}
