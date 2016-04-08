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
namespace OCA\Mail\Service\AutoConfig;

use OCA\Mail\Service\Logger;

class MozillaIspDb {

	/**
	 * @var string
	 */
	private $baseUrl = 'https://autoconfig.thunderbird.net/v1.1/';
	private $logger;

	public function __construct(Logger $logger) {
		$this->logger = $logger;
	}

	/**
	 * @param string $domain
	 * @return array
	 */
	public function query($domain, $tryMx = true) {
		$this->logger->debug("MozillaIsbDb: querying <$domain>");
		if (strpos($domain, '@') !== false) {
			// TODO: use horde mail address parsing instead
			list(, $domain) = explode('@', $domain);
		}

		$url = $this->baseUrl . $domain;

		try {
			$xml = @simplexml_load_file($url);
			if (!is_object($xml) || !$xml->emailProvider) {
				return [];
			}
			$provider = [
				'displayName' => (string) $xml->emailProvider->displayName,
			];
			foreach ($xml->emailProvider->children() as $tag => $server) {
				if (!in_array($tag, ['incomingServer', 'outgoingServer'])) {
					continue;
				}
				foreach ($server->attributes() as $name => $value) {
					if ($name == 'type') {
						$type = (string) $value;
					}
				}
				$data = [];
				foreach ($server as $name => $value) {
					foreach ($value->children() as $tag => $val) {
						$data[$name][$tag] = (string) $val;
					}
					if (!isset($data[$name])) {
						$data[$name] = (string) $value;
					}
				}
				$provider[$type][] = $data;
			}
		} catch (Exception $e) {
			// ignore own not-found exception or xml parsing exceptions
			unset($e);

			if ($tryMx && ($dns = dns_get_record($domain, DNS_MX))) {
				$domain = $dns[0]['target'];
				if (!($provider = $this->query($domain, false))) {
					list(, $domain) = explode('.', $domain, 2);
					$provider = $this->query($domain, false);
				}
			} else {
				$provider = [];
			}
		}
		return $provider;
	}

}
