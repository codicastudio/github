<?php

declare(strict_types=1);

/*
 * This file is part of Laravel GitHub.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\GitHub\Auth\Authenticator;

use DateInterval;
use DateTimeImmutable;
use Github\Client;
use InvalidArgumentException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

/**
 * This is private key github authenticator.
 *
 * @author Pavel Zhytomirsky 
 */
final class PrivateKeyAuthenticator extends AbstractAuthenticator
{
    /**
     * Build JWT token from provided private key file and authenticate with it.
     *
     * @param array $config
     *
     * @throws \Exception
     *
     * @return \Github\Client
     */
    public function authenticate(array $config)
    {
        if (!$this->client) {
            throw new InvalidArgumentException('The client instance was not given to the private key authenticator.');
        }

        if (!array_key_exists('appId', $config)) {
            throw new InvalidArgumentException('The private key authenticator requires the application id to be configured.');
        }

        $issued = new DateTimeImmutable();
        $expires = $issued->add(new DateInterval('PT9M59S'));

        $token = (new Builder())
            ->expiresAt($expires->getTimestamp())
            ->issuedAt($issued->getTimestamp())
            ->issuedBy($config['appId'])
            ->getToken(new Sha256(), self::getKey($config));

        $this->client->authenticate($token, Client::AUTH_JWT);

        return $this->client;
    }

    /**
     * Get the key from the config.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Lcobucci\JWT\Signer\Key
     */
    private static function getKey(array $config)
    {
        if (
            !(array_key_exists('key', $config) || array_key_exists('keyPath', $config)) ||
            (array_key_exists('key', $config) && array_key_exists('keyPath', $config))
        ) {
            throw new InvalidArgumentException('The private key authenticator requires the key or key path to be configured.');
        }

        return new Key(array_key_exists('key', $config) ? $config['key'] : 'file://'.$config['keyPath']);
    }
}
