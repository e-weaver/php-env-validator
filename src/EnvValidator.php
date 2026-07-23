<?php

namespace Eweaver\EnvValidator;

class EnvValidator
{
    private $envFile;
    private $exampleFile;

    public function __construct($envFile = '.env', $exampleFile = '.env.example')
    {
        $this->envFile = $envFile;
        $this->exampleFile = $exampleFile;
    }

    public function run()
    {
        if (!defined('STDIN')) {
            define('STDIN', fopen("php://stdin", "r"));
        }

        if (!file_exists($this->exampleFile)) {
            if (file_exists($this->envFile)) {
                echo "\033[33mWarning: {$this->exampleFile} not found, but {$this->envFile} exists.\033[0m\n";
                echo "Would you like to generate {$this->exampleFile} from your {$this->envFile}? (y/n): ";
                $answer = trim(fgets(STDIN));
                if (strtolower($answer) === 'y') {
                    $this->generateExampleFromEnv();
                    echo "\033[32mSuccessfully generated {$this->exampleFile}\033[0m\n";
                } else {
                    echo "Cannot proceed without {$this->exampleFile}.\n";
                    return 1;
                }
            } else {
                echo "\033[33mWarning: Neither {$this->exampleFile} nor {$this->envFile} found.\033[0m\n";
                echo "Would you like to initialize them now? (y/n): ";
                $answer = trim(fgets(STDIN));
                if (strtolower($answer) === 'y') {
                    file_put_contents($this->exampleFile, "APP_ENV=local\n");
                    file_put_contents($this->envFile, "APP_ENV=local\n");
                    echo "\033[32mInitialized {$this->exampleFile} and {$this->envFile}\033[0m\n";
                } else {
                    echo "Cannot proceed without {$this->exampleFile}.\n";
                    return 1;
                }
            }
        }

        if (!file_exists($this->envFile)) {
            echo "\033[33mWarning: {$this->envFile} not found. Creating from {$this->exampleFile}...\033[0m\n";
            copy($this->exampleFile, $this->envFile);
        }

        $exampleKeys = $this->parseKeys($this->exampleFile);
        $envKeys = $this->parseKeys($this->envFile);

        $missingKeys = array_diff($exampleKeys, $envKeys);

        if (empty($missingKeys)) {
            echo "\033[32mSuccess: Your .env file is up to date!\033[0m\n";
            return 0;
        }

        echo "\033[33mFound " . count($missingKeys) . " missing key(s) in your .env file.\033[0m\n\n";

        foreach ($missingKeys as $key) {
            echo "Enter value for \033[36m{$key}\033[0m (leave blank to skip): ";
            $value = trim(fgets(STDIN));

            if ($value !== '') {
                // If value has spaces, wrap in quotes
                if (strpos($value, ' ') !== false) {
                    $value = '"' . $value . '"';
                }
                file_put_contents($this->envFile, "\n{$key}={$value}", FILE_APPEND);
                echo "\033[32mAdded {$key}\033[0m\n";
            } else {
                echo "\033[33mSkipped {$key}\033[0m\n";
            }
        }

        echo "\n\033[32mValidation complete.\033[0m\n";
        return 0;
    }

    private function generateExampleFromEnv()
    {
        $lines = file($this->envFile, FILE_IGNORE_NEW_LINES);
        $exampleLines = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                $exampleLines[] = $line;
            } elseif (strpos($line, '=') !== false) {
                list($key) = explode('=', $line, 2);
                $exampleLines[] = trim($key) . '=';
            }
        }
        
        file_put_contents($this->exampleFile, implode("\n", $exampleLines) . "\n");
    }

    private function parseKeys($file)
    {
        $keys = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '#') === 0) {
                continue; // Skip comments
            }

            if (strpos($line, '=') !== false) {
                list($key) = explode('=', $line, 2);
                $keys[] = trim($key);
            }
        }

        return $keys;
    }
}
