<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * ownCloud - Mail
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Mail\Tests\Service\Autoconfig;

use OCA\Mail\Service\AutoConfig\IspDb;
use PHPUnit_Framework_TestCase;

class IspDbtest extends PHPUnit_Framework_TestCase {

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	private function invokeMethod(&$object, $methodName, array $parameters = array())
	{
		// Source: https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

	protected function setUp() {
		parent::setUp();

		$logger = $this->getMockBuilder('\OCA\Mail\Service\Logger')
			->disableOriginalConstructor()
			->getMock();
	}


	public function testQueryUrl() {
		$ispDb = new IspDb($this->logger);
		$url = dirname(__FILE__) . '/../../resources/autoconfig-freenet.xml';
		$expected = [] //TODO: fill this with the expected data
		$result = $this->invokeMethod($ispDb, 'queryUrl', $url);
		$this->assertEquals($expected, $result);
	}

	public function testQuery() {
		$ispDb = $this->getMockBuilder('\OCA\Mail\Service\AutoConfig\IspDb')
			->setMethods(array('queryUrl'))
			->getMock();

		$ispDb->expects($this->exactly(3))
			->method('queryUrl')
			->withConsecutive(
				array('https://autoconfig.example.org/mail/config-v1.1.xml'),
				array('https://example.org/.well-known/autoconfig/mail/config-v1.1.xml'),
				array('https://autoconfig.thunderbird.net/v1.1/example.org'),
			);

		$ispDb->query('example.org', false); //TODO: test tryMx = true
	}

}
