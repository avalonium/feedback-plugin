<?php namespace Avalonium\Feedback\Classes;

use Storage;
use Exception;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Models\Unsorted\FormsMetadata;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\Collections\ContactsCollection;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\Collections\Leads\LeadsCollection;
use League\OAuth2\Client\Token\AccessTokenInterface;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;

use Avalonium\Feedback\Models\Settings;
use Avalonium\Feedback\Classes\Amo\AmoConfig;
use Avalonium\Feedback\Classes\Amo\AmoService;

/**
 *
 */
class AmoHelper
{
    const TOKEN_PATH = 'amocrm/token.json';

    /**
     * Amo client
     */
    private AmoCRMApiClient $client;

    /**
     * Collection for leads
     */
    private LeadsCollection $leads;

    /**
     * OAuth access token
     */
    private AccessToken $accessToken;

    /**
     * __construct the class
     */
    public function __construct(string $clientId, string $secretKey)
    {
        $this->leads = new LeadsCollection;
        $this->client = (new AmoCRMApiClientFactory(new AmoConfig($clientId, $secretKey), new AmoService))->make();

        if ($this->checkAccessTokenFile()) {
            $this->accessToken = $this->getAccessToken();
            $this->client->setAccountBaseDomain($this->accessToken->getValues()['base_domain']);
            $this->client->setAccessToken($this->accessToken);
        }
    }

    /**
     * Send Leads to AmoCRM
     *
     * @throws AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     */
    public function sendLeads(): void
    {
        try {
            $this->client->leads()->addComplex($this->leads);
        } catch (AmoCRMApiException $e) {
            throw $e;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Add lead to collection
     */
    public function addLead($data): self
    {
        $lead = new LeadModel();
        $lead->setName(array_get($data, 'number'));

        $lead->setContacts(
            (new ContactsCollection())->add(
                (new ContactModel())
                    ->setFirstName(array_get($data, 'firstname'))
                    ->setLastName(array_get($data, 'lastname'))
                    ->setCustomFieldsValues(
                        (new CustomFieldsValuesCollection())
                            ->add((new MultitextCustomFieldValuesModel())
                                ->setFieldCode('PHONE')
                                ->setValues((new MultitextCustomFieldValueCollection())
                                    ->add((new MultitextCustomFieldValueModel())
                                        ->setValue(array_get($data, 'phone'))))))));

        // Metadata
        $meta = new FormsMetadata();
        $meta->setFormId('oc_form_id');
        $meta->setFormName('Form name');
        $meta->setIp(array_get($data, 'ip'));
        $meta->setFormPage(array_get($data, 'referer'));
        $lead->setMetadata($meta);

        $lead->setPipelineId(array_get($data, 'amo_pipeline_id'));
        $lead->setStatusId(array_get($data, 'amo_pipeline_status_id'));

        // Add lead to collection
        $this->leads->add($lead);

        return $this;
    }

    /**
     * Get pipelines list
     *
     * @throws AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     */
    public function getPipelinesList(): array
    {
        $list = [];

        foreach ($this->client->pipelines()->get() as $pipeline) {
            $list[$pipeline->getId()] = $pipeline->getName();
        }

        return $list;
    }

    /**
     * Get pipeline statuses list
     */
    public function getPipelineStatusesList($pipelineId): array
    {
        $list = [];

        if ($pipelineId) {
            foreach ($this->client->statuses($pipelineId)->get() as $status) {
                $list[$status->getId()] = $status->getName();
            }
        }

        return $list;
    }

    /**
     * Check OAuth access token
     */
    public function isAccessTokenSet(): bool
    {
        return $this->client->isAccessTokenSet();
    }

    /**
     * Get OAuth button
     */
    public function getOAuthButton(array $options = []): string
    {
        return $this->client->getOAuthClient()->getOAuthButton($options);
    }

    /**
     * Get OAuth access token owner email
     */
    public function getAccessTokenOwnerEmail(): string|null
    {
        if ($this->isAccessTokenSet()) {
            return $this->client->getOAuthClient()->getResourceOwner($this->accessToken)->getEmail();
        }

        return null;
    }

    /**
     * Update OAuth access token
     */
    public function updateAccessToken(string $code, string $domain): bool
    {
        return $this->saveAccessToken(array_merge(
            ['base_domain' => $domain],
            $this->client->setAccountBaseDomain($domain)->getOAuthClient()->getAccessTokenByCode($code)->jsonSerialize()
        ));
    }

    /**
     * Check if OAuth access token file is exists
     */
    private function checkAccessTokenFile(): bool
    {
        return Storage::exists(self::TOKEN_PATH);
    }

    /**
     *  Get OAuth access token from file
     */
    private function getAccessToken(): AccessTokenInterface|null
    {
        return new AccessToken(json_decode(Storage::get(self::TOKEN_PATH), true));
    }

    /**
     * Save OAuth access token
     */
    private function saveAccessToken(array $accessToken): bool
    {
        return Storage::put(self::TOKEN_PATH, json_encode($accessToken));
    }

    /**
     * Remove OAuth access token
     */
    public static function removeOAuthAccessToken()
    {
        return Storage::delete(self::TOKEN_PATH);
    }

    /**
     * Create AmoHelper instance
     */
    public static function create(): self
    {
        return new self(Settings::get('amo_client_id'), Settings::get('amo_client_key'));
    }
}
