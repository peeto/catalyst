<?php

namespace catalyst\userupload;

class UserUpload
{
	protected static function createTable($db): void {
		$sql = "CREATE TABLE IF NOT EXISTS `users` (
		  `name` VARCHAR(1024) NOT NULL,
		  `surname` VARCHAR(1024) NOT NULL,
		  `email` VARCHAR(1024) NOT NULL,
		  UNIQUE INDEX `email` (`email` ASC) VISIBLE,
		  INDEX `name` (`name` ASC) VISIBLE,
		  INDEX `surname` (`surname` ASC) VISIBLE);";
		
		if (!$db->query($sql)) {
			echo "\r\nError: " . $db->error. "\r\n\r\n";
		} else {
			echo "\r\nTable `users` created.\r\n\r\n";
		}
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
			// @todo do dry run
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

			$db = new \mysqli(
				$opts['h'],
				$opts['u'],
				$opts['p'],
				$opts['d']);
			$db->query('USE "' . $opts['d'] . '";');

			if (isset($opts['create_table'])) {
				self::createTable($db);
				return;
			}

			if (isset($opts['file'])) {
				// @todo import file
				return;
			}
		}

		echo "\r\nCommand not understood\r\n";
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
