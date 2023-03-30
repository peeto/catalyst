<?php

namespace catalyst\userupload;

class UserUpload
{
	protected $db;

	protected function parseCommand(): void {
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

		echo "\r\nCommand not understood\r\n";
		self::writeHelp();
	}

	protected function writeHelp(): void {
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

			-d – MySQL database

			-h – MySQL host

			--help – which will output the above list of directives with details.


		EOT;
	}

	public function init(): void {
		self::parseCommand();
	}
}

UserUpload::init();
