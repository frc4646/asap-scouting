<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP )
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2015 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     0.3.0
 */

namespace app\Helpers;

use Google_Client;
use Exception;

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;

use Slim\Slim;

class GoogleAPI
{
    protected $config;

    protected $app;

    private $credentials_file;

    protected $sheetID;

    protected $client;

    protected $spreadsheet;

    /**
     * Get values from application
     *
     * @param object $app Slim Application Variable
     */
    public function __construct(Slim $app)
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
        $this->client->setScopes(["https://www.googleapis.com/auth/drive", "https://spreadsheets.google.com/feeds"]);

        $this->client->fetchAccessTokenWithAssertion($this->client->getHttpClient());

        $serviceRequest = new DefaultServiceRequest($this->client->getAccessToken()["access_token"]);
        ServiceRequestFactory::setInstance($serviceRequest);

        $spreadsheetService = new SpreadsheetService();
        $this->spreadsheet = $spreadsheetService->getSpreadsheetById($this->sheetID);
    }

    /**
     * Gets the sheet by a name
     *
     * @param  string $name Sheet Name
     *
     * @return object The worksheet with that name
     */
    public function getSheet($name = "Quals")
    {
        $worksheetFeed = $this->spreadsheet->getWorksheets();
        $worksheet = $worksheetFeed->getByTitle($name);

        return $worksheet;
    }

    /**
     * Sets a new value on the spreadsheet
     *
     * @param  integer $match   What match is it
     * @param  string  $column  The Column name
     * @param  integer $value   The new value to set
     *
     * @return boolean Return if the update was successful
     */
    public function setValue($match = 5, $column = "redscore", $value = 12)
    {
        $cellFeed = $this->getSheet("Quals")->getCellFeed();
        $entries = $this->getSheet("Quals")->getListFeed()->getEntries();

        $values = [];
        $new = [];
        $i = 0;
        $z = 0;
        foreach ($entries as $entry) {
            $values = $entry->getValues();
            if ($values["match"] == $match) {
                $values[$column] = $value;
                $new = $values;
                $z = $i;
            }
            $i++;
        }

        return (bool) is_null($entries[$z]->update($new));
    }
}
