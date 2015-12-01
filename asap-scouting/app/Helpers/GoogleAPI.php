<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP )
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2015 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     0.1.0
 */

namespace app\Helpers;

use Google_Client;
use Exception;

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;

class GoogleAPI
{
    protected $config;

    protected $app;

    private $credentials_file;

    protected $sheetID;

    protected $client;

    protected $spreadsheet;

    public function __construct($app)
    {
        $this->app = $app;
        $this->config = $this->app->config;
        $this->credentials = $this->config->get("api.google_api_credentials_filename");
        $this->sheetID = $this->config->get("api.sheetID");

        $this->client = new Google_Client();

        if (file_exists(INC_ROOT . "/{$this->credentials}")) {
            $this->client->setAuthConfig(INC_ROOT . "/{$this->credentials}");
        } else {
            throw new Exception("API File not found");
        }

        $this->client->setApplicationName("Sheets API Testing");
        $this->client->setScopes(['https://www.googleapis.com/auth/drive', 'https://spreadsheets.google.com/feeds']);

        $this->client->fetchAccessTokenWithAssertion($this->client->getHttpClient());

        $serviceRequest = new DefaultServiceRequest($this->client->getAccessToken()["access_token"]);
        ServiceRequestFactory::setInstance($serviceRequest);

        $spreadsheetService = new SpreadsheetService();
        $this->spreadsheet = $spreadsheetService->getSpreadsheets();
    }

    public function getSheet()
    {
        $worksheetFeed = $this->spreadsheet->getById("https://spreadsheets.google.com/feeds/spreadsheets/private/full/1uUMuvUWHpvNYUTWJIc4RdqXg-iGY-ijfpYvhgC3gX9I")->getWorksheets();
        $worksheet = $worksheetFeed->getByTitle('Quals');

        return $worksheet;
    }

}
