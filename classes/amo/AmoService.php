<?php namespace Avalonium\Feedback\Classes\Amo;

use Log;
use Storage;
use AmoCRM\OAuth\OAuthServiceInterface;
use Avalonium\Feedback\Classes\AmoHelper;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Amo config class
 */
class AmoService implements OAuthServiceInterface
{
    public function saveOAuthToken(AccessTokenInterface $accessToken, string $baseDomain): void
    {
        Log::info('Call saveOAuthToken');

        Storage::put(AmoHelper::TOKEN_PATH, json_encode(array_merge(
            $accessToken->jsonSerialize(), ['base_domain' => $baseDomain]
        )));
    }
}
