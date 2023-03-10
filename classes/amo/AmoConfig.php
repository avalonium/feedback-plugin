<?php namespace Avalonium\Feedback\Classes\Amo;

/**
 * Amo config class
 */
class AmoConfig implements \AmoCRM\OAuth\OAuthConfigInterface
{
    /**
     * Amo client ID
     */
    private string $clientId;

    /**
     * Amo secret key
     */
    private string $secretKey;

    /**
     * __construct the class
     */
    public function __construct(string $clientId, string $secretKey)
    {
        $this->clientId = $clientId;
        $this->secretKey = $secretKey;
    }

    public function getIntegrationId(): string
    {
        return $this->clientId;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getRedirectDomain(): string
    {
        return route('api.amo.token.update');
    }
}
