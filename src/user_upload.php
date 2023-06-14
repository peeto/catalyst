<?php

namespace catalyst\userupload;

class UserUpload
{
    protected static function createTable($db): void {
        $sql = "CREATE TABLE `users` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `surname` varchar(255) NOT NULL,
            `email` varchar(320) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `id_UNIQUE` (`id`),
            UNIQUE KEY `email` (`email`),
            KEY `name` (`name`),
            KEY `surname` (`surname`)
          ) ENGINE=InnoDB;
        ";

        if (!$db->query($sql)) {
            echo "\r\nError: " . $db->error. "\r\n\r\n";
        } else {
            echo "\r\nTable `users` created or exists.\r\n\r\n";
        }
    }
    
    protected static function isCSVFile(string $file): bool {
        $csvTypes = array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
            );
        $isCSV = false;
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file);
        $isCSV =  in_array($mime, $csvTypes) === true;
        finfo_close($finfo);
        
        return $isCSV;
    }

    protected static function parseCSVFile(string $file): array {
        if (!file_exists($file)) {
            echo "\r\nFile $file does not exist.\r\n\r\n";
            exit();
        }
        if (!self::isCSVFile($file)) {
            echo "\r\nFile $file is not of type CSV.\r\n\r\n";
            exit();
        }
        
        $results = [];
        try {
            if (($handle = fopen($file, "r")) !== FALSE) {
                $ignoreHeader = true;
                while (($data = fgetcsv($handle, 1024, ",")) !== FALSE) {
                    if ($ignoreHeader) {
                        $ignoreHeader = false;
                    } else  {
                        $results[] = $data;
                    }
                }
            }
        } catch (Exception $ex) {
            echo "Could not parse CSV in $file. Possible wrong file, record structure or encoding.\r\n";
            exit();
        }
        return $results;
    }

    protected static function parseName(string $name): string {
        return trim(ucfirst(strtolower($name)));
    }

    protected static function checkEmail(string $email): bool {
        return !!filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    protected static function parseEmail(string $email): string {
        return trim(strtolower($email));
    }

    protected static function dryRun(string $file): void {
        $data = self::parseCSVFile($file);

        echo "\r\n". count($data) . " records found.\r\n\r\n";
        $errors = 0;

        try {
            $record = 1;
            foreach ($data as $r) {
                @list($name, $surname, $email) = $r;
                if ($name == '' || $surname == '' || $email == '') {
                    echo "Empty required data found on record $record.\r\n";
                    $errors++;
                    continue;
                }
                $name = self::parseName($name);
                $surname = self::parseName($surname);
                $email = self::parseEmail($email);
                
                echo "Firstname: $name";
                echo " Surname: $surname";
                if (!self::checkEmail($r[2])) {
                    echo " Invalid email address found!";
                    $errors++;
                }
                echo " Email: $email";
                echo "\r\n";
                $record++;
            }
            echo "\r\n";
        } catch (Exception $ex) {
            echo "General decoding error. Is CSV file $file correct?\r\n\r\n";
            exit();
        }
        echo "\r\n". $errors . " errors found.\r\n\r\n";
        
    }

    protected static function dbUpload($db, string $file): void {

        $data = self::parseCSVFile($file);

        echo "\r\n". count($data) . " records found.\r\n\r\n";
        $errors = 0;
        
        try {
            $record = 1;
            foreach ($data as $r) {
                @list($name, $surname, $email) = $r;
                if ($name == '' || $surname == '' || $email == '') {
                    echo "Empty required data found on record $record.\r\n";
                    $errors++;
                    continue;
                }

                if (!self::checkEmail($email)) {
                    $errors++;
                    echo "Invalid email address found: " . self::parseEmail($email) . "\r\n";
                } else {
                    $name = self::parseName($name);
                    $surname = self::parseName($surname);
                    $email = self::parseEmail($email);

                    try {
                        $sql = "INSERT INTO users (name, surname, email) VALUES (?, ?, ?);";
                        $stmt = $db->prepare($sql);
                        $stmt->bind_param("sss", $name, $surname, $email);
                        $stmt->execute();
                        if (!$stmt->insert_id) {
                            $errors++;
                            echo "Could not insert record for $email. Possible dupliate?\r\n";
                        }
                    } catch (Exception $ex) {
                        $errors++;
                        echo "Could not insert record for $email. Possible dupliate?\r\n";
                    }
                }
                $record++;
            }
        } catch (Exception $ex) {
            echo "General decoding error. Is CSV file $file correct?\r\n\r\n";
            exit();
        }
        echo "\r\n". $errors . " errors found.\r\n\r\n";
    }

    protected static function parseCommand(): void {
        $opts = getopt('u:p:h:d:', [
            'file:',
            'create_table',
            'dry_run',
            'help'
        ]);

        if (isset($opts['help'])) {
            self::writeHelp();
            return;
        }

        if (isset($opts['dry_run']) && !isset($opts['file'])) {
            echo "\r\n'dry_run' requires 'file' to be specified. Consult help for more information.\r\n\r\n";
            return;
        }

        if (isset($opts['dry_run']) && isset($opts['file'])) {
            self::dryRun($opts['file']);
            return;
        }

        $hasDbOpts = isset($opts['u'])
            && isset($opts['p'])
            && isset($opts['d'])
            && isset($opts['h']);

        if (isset($opts['create_table']) || isset($opts['file'])) {
            if (!$hasDbOpts) {
                echo "\r\nAll MySQL options must be provided. Consult help for more information.\r\n\r\n";
                return;
            }

            try {
                $db = new \mysqli(
                    $opts['h'],
                    $opts['u'],
                    $opts['p'],
                    $opts['d']);
                if ($db->connect_errno) {
                    echo "Failed to connect to MySQL: " . $db->connect_error . "\r\n\r\n";
                    exit();
                }
                $db->query('USE "' . $db->real_escape_string($opts['d']) . '";');
            } catch (Exception $ex) {
                echo "Could not connect to database, check parameters and try again.\r\n";
                die();
            }

            if (isset($opts['create_table'])) {
                self::createTable($db);
                return;
            }

            if (isset($opts['file'])) {
                self::dbUpload($db, $opts['file']);
                return;
            }
        }

        echo "\r\nCommand not understood.\r\n";
        self::writeHelp();
    }

    protected static function writeHelp(): void {
        echo "\r\nphp -f src/user_upload.php --\r\n";
        echo "\r\nAvailable options are:\r\n";
        echo <<< EOT

            --file [csv file name] – this is the name of the CSV to be parsed

            --create_table – this will cause the MySQL users table to be built (and no further
                    action will be taken)

            --dry_run – this will be used with the --file directive in case we want to run the script but not
                    insert into the DB. All other functions will be executed, but the database wont be altered

            -u – MySQL username

            -p – MySQL password

            -d – MySQL database name

            -h – MySQL host

            --help – which will output the above list of directives with details.


        EOT;
    }

    public static function init(): void {
        self::parseCommand();
    }
}

UserUpload::init();
