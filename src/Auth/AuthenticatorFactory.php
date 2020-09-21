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

namespace GrahamCampbell\GitHub\Auth;

use InvalidArgumentException;

/**
 * This is the authenticator factory class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class AuthenticatorFactory
{
    /**
     * Make a new authenticator instance.
     *
     * @param string $method
     *
     * @throws \InvalidArgumentException
     *
     * @return \GrahamCampbell\GitHub\Auth\Authenticator\AuthenticatorInterface
     */
    public function make(string $method)
    {
        switch ($method) {
            case 'application':
                return new Authenticator\ApplicationAuthenticator();
            case 'jwt':
                return new Authenticator\JwtAuthenticator();
            case 'private':
                return new Authenticator\PrivateKeyAuthenticator();
            case 'password':
                return new Authenticator\PasswordAuthenticator();
            case 'token':
                return new Authenticator\TokenAuthenticator();
        }

        throw new InvalidArgumentException("Unsupported authentication method [$method].");
    }
}
