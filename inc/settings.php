<?php

final class Settings {
    public static $TRUE = "1", $FALSE = "0";

    public function __construct($connect = true) {
        // Web interface language. Languages are stored in the "lang/" directory.
        $this->lang = 'en_US';

        // Server name, shown on the main page and on the header
        $this->name = 'LiteBans';

        // Clicking on the header name will send you to this address.
        $this->name_link = '#';

        // Database information
        $host = 'localhost';
        $port = 3306;

        $database = 'litebans';

        $username = '';
        $password = '';

        // If you set a table prefix in config.yml, set it here as well
        $table_prefix = "litebans_";

        // Supported drivers: mysql, pgsql
        $driver = 'mysql';

        // Show inactive bans? Removed bans will show (Unbanned), mutes will show (Unmuted), warnings will show (Expired).
        $this->show_inactive_bans = true;

        // Show pager? This allows users to page through the list of bans.
        $this->show_pager = true;

        // Amount of bans/mutes/warnings to show on each page
        $this->limit_per_page = 10;

        // The server console will be identified by any of these names.
        // It will be given a standard name and avatar image.
        $this->console_aliases = array(
            "CONSOLE", "Console",
        );
        $this->console_name = "Console";
        $this->console_image = "inc/img/console.png";

        // Avatar images for all players will be fetched from this URL.
        // Examples:
        /* 'https://cravatar.eu/avatar/$UUID/25'
         * 'https://crafatar.com/avatars/$UUID?size=25'
         * 'https://minotar.net/avatar/$NAME/25'
         */
        $this->avatar_source = 'https://cravatar.eu/avatar/$UUID/25';

        // If enabled, names will be shown below avatars instead of being shown next to them.
        $this->avatar_names_below = true;

        // If enabled, the total amount of bans, mutes, warnings, and kicks will be shown next to the buttons in the header.
        $this->header_show_totals = true;

        // The date format can be changed here.
        // https://secure.php.net/manual/en/function.strftime.php
        // Example of default: July 2, 2015, 9:19 PM
        $this->date_format = '%B %d, %Y, %H:%I %p';
        date_default_timezone_set("UTC");

        // Enable PHP error reporting.
        $error_reporting = true;

        // Enable error pages.
        $this->error_pages = true;

        /*** End of configuration ***/


        /** Don't modify anything here unless you know what you're doing **/

        if ($error_reporting) {
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        }

        $this->table = array(
            'bans'     => "${table_prefix}bans",
            'mutes'    => "${table_prefix}mutes",
            'warnings' => "${table_prefix}warnings",
            'kicks'    => "${table_prefix}kicks",
            'history'  => "${table_prefix}history",
        );

        $this->active_query = "";

        if ($driver === "pgsql") {
            Settings::$TRUE = "B'1'";
            Settings::$FALSE = "B'0'";
        }

        if (!$this->show_inactive_bans) {
            $this->active_query = "WHERE active=" . Settings::$TRUE;
        }

        $this->driver = $driver;
        if ($connect) {
            if ($username === "" && $password === "") {
                $this->redirect("error/unconfigured.php");
            }

            $dsn = "$driver:dbname=$database;host=$host;port=$port";
            if ($driver === 'mysql') {
                $dsn .= ';charset=utf8';
            }

            $options = array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            );

            try {
                $this->conn = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                Settings::handle_error($this, $e);
            }
            if ($driver === 'pgsql') {
                $this->conn->query("SET NAMES 'UTF8';");
            }
        }
    }


    /**
     * @param $settings Settings
     * @param $e Exception
     */
    static function handle_error($settings, $e) {
        $message = $e->getMessage();
        if ($settings->error_pages) {
            if (strstr($message, "Access denied for user")) {
                $settings->redirect("error/access-denied.php?error=" . base64_encode($message));
            }
            if (strstr($message, "Base table or view not found:")) {
                $settings->redirect("error/tables-not-found.php");
            }
        }
        die('Database error: ' . $message);
    }


    function redirect($url) {
        echo "<a href=\"$url\">Redirecting...</a>";

        echo "<script type=\"text/javascript\">document.location=\"$url\";</script>";

        die;
    }
}
