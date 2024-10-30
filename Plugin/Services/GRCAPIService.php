<?php

namespace SGPLUGIN\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * A service that provide tool to send a contact to the API of GRC Contact CRM
 *
 * Class GRCAPIService
 * @package SGPLUGIN\Services
 */
class GRCAPIService
{
    private $client;
    private $url = 'https://www.grc-contact.fr/index.php?m=apiWeb&c=CtrCompany&a=';
    private $actions = [
        'post' => 'doInsert'
    ];
    private $accountKey = null;
    private $password = null;
    private $credentials = [];

    /**
     * GRCAPIService constructor.
     * @param string $accountKey
     * @param string $password
     */
    public function __construct(string $accountKey, string $password)
    {
        $this->client = new Client();
        $this->accountKey = $accountKey;
        $this->password = $password;
        $this->credentials = [
            'account_key'  => $this->accountKey,
            'password'     => $this->password,
            'output'       => 'JSON',
        ];
    }

    /**
     * Send contact to the CRM GRC Contact by curl request using Guzzle.
     *
     * @param array $datas
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendContact(array $datas = []):ResponseInterface {
        $datas = array_merge(
            $this->credentials + [
                'origin' => 'Formulaire de contact',
                'company' => null,
                'group_id' => NULL,
                'title' => 1,
                'firstname' => null,
                'lastname' => null,
                'city' => null,
                'zip_code' => null,
                'country' => 'France',
                'email' => null,
                'phone' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'et_prs2_superficie' => null,
                'et_prs3_partie_isoler' => null,
                'et_prs4_quand' => null,
                'custom_field_names' => 'et_prs2_superficie|et_prs3_partie_isoler|et_prs4_quand',
                'custom_field_values' => null,
            ],
            $datas
        );

        return $this->client->post(
            $this->url.$this->actions['post'], [
                'form_params' => $datas,
            ]
        );
    }
}